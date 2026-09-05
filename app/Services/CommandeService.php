<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\StatutCommande;
use App\Enums\StatutPaiement;
use App\Exceptions\CommandeInexistanteException;
use App\Exceptions\CommandeInvalideException;
use App\Exceptions\ProduitInexistantException;
use App\Exceptions\StockInsuffisantException;
use App\Exceptions\TransitionStatutInvalideException;
use App\Models\Commande;
use App\Models\LigneCommande;
use App\Repositories\CommandeRepository;
use App\Repositories\LigneCommandeRepository;
use App\Repositories\ProduitRepository;
use Core\Database;
use DateTimeImmutable;

/*
 * Applique les règles métier liées aux commandes, pour les deux origines
 * possibles : validation du panier d'un client (flux web classique) et
 * enregistrement d'une commande sur place par un Gérant (même logique que
 * CommandeService.java côté Java Console). Les deux flux partagent la
 * même méthode de création en base, seule leur préparation diffère.
 */
final class CommandeService
{
    public function __construct(
        private readonly CommandeRepository $commandeRepository,
        private readonly LigneCommandeRepository $ligneCommandeRepository,
        private readonly ProduitRepository $produitRepository,
        private readonly ProduitService $produitService,
        private readonly FactureService $factureService,
    ) {
    }

    /**
     * Retourne toutes les commandes.
     *
     * @return Commande[] Liste de toutes les commandes
     */
    public function listerCommandes(): array
    {
        return $this->commandeRepository->trouverTous();
    }

    /**
     * Retourne l'historique des commandes d'un client donné.
     *
     * @param int $clientId Identifiant du client recherché
     * @return Commande[] Commandes de ce client
     */
    public function listerCommandesClient(int $clientId): array
    {
        return $this->commandeRepository->trouverParClient($clientId);
    }

    /**
     * Retourne les commandes ayant un statut donné.
     *
     * @param StatutCommande $statut Statut recherché
     * @return Commande[] Commandes correspondantes
     */
    public function filtrerParStatut(StatutCommande $statut): array
    {
        return $this->commandeRepository->trouverParStatut($statut);
    }

    /**
     * Récupère une commande par son identifiant, avec ses lignes chargées.
     *
     * @param int $id Identifiant de la commande recherchée
     * @return Commande|null La commande trouvée, ou null si elle n'existe pas
     */
    public function consulterCommande(int $id): ?Commande
    {
        $commande = $this->commandeRepository->trouverParId($id);

        if ($commande !== null) {
            $commande->definirLignes($this->ligneCommandeRepository->trouverParCommande($id));
        }

        return $commande;
    }

    /**
     * Recherche une commande par son numéro lisible.
     *
     * @param string $numeroCommande Numéro de commande recherché
     * @return Commande La commande trouvée
     *
     * @throws CommandeInexistanteException si aucune commande ne correspond
     */
    public function rechercherParNumero(string $numeroCommande): Commande
    {
        $commande = $this->commandeRepository->trouverParNumero($numeroCommande);

        if ($commande === null) {
            throw new CommandeInexistanteException("Aucune commande avec le numéro {$numeroCommande}.");
        }

        return $commande;
    }

    /**
     * Transforme le panier d'un client en commande — flux principal du
     * site web (US "Passer une commande"). Le panier est fourni sous la
     * forme [produitId => quantite], déjà validé côté PanierService quant
     * à sa non-vacuité. La commande créée démarre systématiquement à
     * EN_ATTENTE / IMPAYE, contrairement à une vente sur place.
     *
     * L'ensemble de l'opération (création de la commande, de ses lignes,
     * et diminution du stock) est exécutée dans une transaction : toute
     * erreur en cours de route (ex: stock insuffisant sur une ligne)
     * annule l'intégralité des écritures déjà effectuées, pour ne jamais
     * laisser de commande partiellement enregistrée.
     *
     * @param int                $clientId      Identifiant du client passant commande
     * @param array<int, int>    $lignesPanier  Quantités demandées, indexées par identifiant de produit
     * @return Commande La commande créée
     *
     * @throws CommandeInvalideException si le panier est vide
     * @throws ProduitInexistantException si un produit du panier n'existe plus
     * @throws StockInsuffisantException si une quantité dépasse le stock disponible
     */
    public function validerPanier(int $clientId, array $lignesPanier): Commande
    {
        if (empty($lignesPanier)) {
            throw new CommandeInvalideException('La commande doit contenir au moins un article.');
        }
    
        return $this->creerCommande($clientId, $lignesPanier, StatutCommande::EN_ATTENTE);
    }

    /**
     * Enregistre une vente au comptoir directement par un Gérant —
     * équivalent PHP de CommandeService.creerCommandeSurPlace() côté Java
     * Console. Contrairement au flux web, la commande démarre directement
     * à PRETE ou RETIREE (choisi par le Gérant), et une facture est
     * générée automatiquement.
     *
     * @param int             $clientId       Identifiant du client concerné
     * @param array<int, int> $lignesSaisies  Quantités saisies, indexées par identifiant de produit
     * @param StatutCommande  $statutInitial  Statut de départ (PRETE ou RETIREE)
     * @return Commande La commande créée
     *
     * @throws CommandeInvalideException si aucune ligne n'a été saisie
     * @throws ProduitInexistantException si un produit saisi n'existe pas
     * @throws StockInsuffisantException si une quantité dépasse le stock disponible
     */
    public function creerCommandeSurPlace(int $clientId, array $lignesSaisies, StatutCommande $statutInitial): Commande
    {
        if (empty($lignesSaisies)) {
            throw new CommandeInvalideException('Une commande doit contenir au moins un article.');
        }

        return $this->creerCommande($clientId, $lignesSaisies, $statutInitial);
    }

    /**
     * Logique commune aux deux flux de création de commande : ouvre une
     * transaction, crée la commande et ses lignes, diminue le stock ligne par
     * ligne, calcule le montant total, génère la facture correspondante, puis
     * valide la transaction. Chaque commande reçoit systématiquement sa
     * facture, quelle que soit son origine (panier client ou vente sur place).
     *
     * @param int             $clientId      Identifiant du client concerné
     * @param array<int, int> $lignes        Quantités demandées, indexées par identifiant de produit
     * @param StatutCommande  $statutInitial Statut à la création
     * @return Commande La commande créée
     *
     * @throws ProduitInexistantException si un produit demandé n'existe pas
     * @throws StockInsuffisantException si une quantité dépasse le stock disponible
     */
    private function creerCommande(int $clientId, array $lignes, StatutCommande $statutInitial): Commande
    {
        $connexion = Database::getConnexion();
        $connexion->beginTransaction();
    
        try {
            $commande = new Commande(
                0,
                $this->genererNumeroCommande(),
                $clientId,
                new DateTimeImmutable(),
                $statutInitial,
                StatutPaiement::IMPAYE,
                0.0
            );
            $commande = $this->commandeRepository->creer($commande);
    
            $montantTotal = 0.0;
    
            foreach ($lignes as $produitId => $quantite) {
                $produit = $this->produitRepository->trouverParId($produitId);
    
                if ($produit === null) {
                    throw new ProduitInexistantException("Produit introuvable avec l'id {$produitId}.");
                }
    
                if ($quantite > $produit->getQuantiteStock()) {
                    throw new StockInsuffisantException(
                        "Stock insuffisant pour {$produit->getLibelle()} (disponible : {$produit->getQuantiteStock()})."
                    );
                }
    
                $ligneCommande = new LigneCommande(0, $commande->getId(), $produitId, $quantite, $produit->getPrix());
                $this->ligneCommandeRepository->creer($ligneCommande);
    
                $this->produitService->diminuerStock($produitId, $quantite);
    
                $montantTotal += $ligneCommande->calculerSousTotal();
            }
    
            $commande->setMontantTotal($montantTotal);
            $this->commandeRepository->mettreAJour($commande);
    
            $this->factureService->genererFacture($commande);
    
            $connexion->commit();
    
            return $commande;
        } catch (\Throwable $exception) {
            $connexion->rollBack();
    
            throw $exception;
        }
    }

    /**
     * Change le statut de préparation d'une commande. Le passage à ANNULEE
     * est accepté depuis n'importe quel statut et restitue automatiquement
     * le stock des produits concernés ; toute autre transition doit suivre
     * l'enchaînement EN_ATTENTE -> EN_PREPARATION -> PRETE -> RETIREE.
     *
     * @param int            $commandeId    Identifiant de la commande concernée
     * @param StatutCommande $nouveauStatut Statut à appliquer
     *
     * @throws CommandeInexistanteException si la commande n'existe pas
     * @throws TransitionStatutInvalideException si la transition n'est pas autorisée
     */
    public function changerStatut(int $commandeId, StatutCommande $nouveauStatut): void
    {
        $commande = $this->commandeRepository->trouverParId($commandeId);

        if ($commande === null) {
            throw new CommandeInexistanteException("Commande introuvable avec l'id {$commandeId}.");
        }

        if ($nouveauStatut === StatutCommande::ANNULEE) {
            $this->restaurerStockDeLaCommande($commandeId);
        } else {
            $this->verifierTransitionValide($commande->getStatut(), $nouveauStatut);
        }

        $commande->changerStatut($nouveauStatut);
        $this->commandeRepository->mettreAJour($commande);
    }

    /**
     * Restitue le stock de chaque ligne d'une commande annulée.
     *
     * @param int $commandeId Identifiant de la commande annulée
     */
    private function restaurerStockDeLaCommande(int $commandeId): void
    {
        $lignes = $this->ligneCommandeRepository->trouverParCommande($commandeId);

        foreach ($lignes as $ligne) {
            $this->produitService->restaurerStock($ligne->getProduitId(), $ligne->getQuantite());
        }
    }

    /**
     * Vérifie qu'une transition de statut respecte l'enchaînement autorisé
     * (hors annulation, traitée séparément par changerStatut()).
     *
     * @param StatutCommande $statutActuel  Statut courant de la commande
     * @param StatutCommande $nouveauStatut Statut demandé
     *
     * @throws TransitionStatutInvalideException si la transition n'est pas autorisée
     */
    private function verifierTransitionValide(StatutCommande $statutActuel, StatutCommande $nouveauStatut): void
    {
        $valide = match ($statutActuel) {
            StatutCommande::EN_ATTENTE => $nouveauStatut === StatutCommande::EN_PREPARATION,
            StatutCommande::EN_PREPARATION => $nouveauStatut === StatutCommande::PRETE,
            StatutCommande::PRETE => $nouveauStatut === StatutCommande::RETIREE,
            StatutCommande::RETIREE, StatutCommande::ANNULEE => false,
        };

        if (!$valide) {
            throw new TransitionStatutInvalideException(
                "Transition impossible de {$statutActuel->value} vers {$nouveauStatut->value}."
            );
        }
    }

    /**
     * Génère un numéro de commande lisible du type CMD-2026-000231, sur le
     * même principe que les numéros de facture et de reçu.
     *
     * @return string Numéro de commande généré
     */
    private function genererNumeroCommande(): string
    {
        $nombreCommandes = count($this->commandeRepository->trouverTous());
        $annee = (new DateTimeImmutable())->format('Y');

        return sprintf('CMD-%s-%06d', $annee, $nombreCommandes + 1);
    }
}