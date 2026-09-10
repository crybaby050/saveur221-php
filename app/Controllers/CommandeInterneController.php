<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Enums\StatutCommande;
use App\Exceptions\CommandeInexistanteException;
use App\Exceptions\CommandeInvalideException;
use App\Exceptions\ProduitInexistantException;
use App\Exceptions\StockInsuffisantException;
use App\Exceptions\TransitionStatutInvalideException;
use App\Services\AuthService;
use App\Services\ClientService;
use App\Services\CommandeService;
use App\Services\ProduitService;
use Core\Response;

/*
 * Gère les commandes depuis l'espace interne (Gérant/Admin) : consultation,
 * recherche, filtrage, changement de statut, annulation, et création d'une
 * commande au comptoir pour un client identifié par téléphone.
 */
final class CommandeInterneController extends ControllerInterneBase
{
    public function __construct(
        private readonly CommandeService $commandeService,
        private readonly ClientService $clientService,
        private readonly ProduitService $produitService,
        AuthService $authService,
    ) {
        parent::__construct($authService);
    }

    /**
     * Affiche la liste des commandes, avec filtrage par statut optionnel.
     */
    public function index(): void
    {
        $this->exigerUtilisateurConnecte();

        $statutParam = $_GET['statut'] ?? null;
        $commandes = $statutParam !== null
            ? $this->commandeService->filtrerParStatut(StatutCommande::from($statutParam))
            : $this->commandeService->listerCommandes();

        $this->afficherVueInterne('gerant/commandes/index', ['commandes' => $commandes]);
    }

    /**
     * Recherche une commande par son numéro lisible.
     */
    public function rechercher(): void
    {
        $this->exigerUtilisateurConnecte();

        try {
            $commande = $this->commandeService->rechercherParNumero($_GET['numero'] ?? '');

            $this->afficherVueInterne('gerant/commandes/detail', [
                'commande' => $commande,
                'client' => $this->clientService->consulterClient($commande->getClientId()),
                'lignesEnrichies' => $this->enrichirLignes($commande),
            ]);
        } catch (CommandeInexistanteException $exception) {
            $this->afficherVueInterne('gerant/commandes/index', [
                'commandes' => $this->commandeService->listerCommandes(),
                'erreur' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * Affiche le formulaire (wizard) de création d'une commande au
     * comptoir. La recherche de client se fait désormais en JS via
     * rechercherClientsJson() ; cette action se contente de fournir le
     * catalogue de produits nécessaire à l'étape 2.
     */
    public function afficherCreation(): void
    {
        $this->exigerUtilisateurConnecte();

        $this->afficherVueInterne('gerant/commandes/nouvelle', [
            'produits' => $this->produitService->listerProduits(),
        ]);
    }

    /**
     * Recherche des clients par fragment de téléphone, au format JSON —
     * consommée par le JS du formulaire de création de commande pour
     * afficher des suggestions en direct.
     */
    public function rechercherClientsJson(): void
    {
        $this->exigerUtilisateurConnecte();

        $telephone = trim($_GET['telephone'] ?? '');

        $clients = $telephone !== ''
            ? $this->clientService->rechercherParTelephone($telephone)
            : [];

        Response::json(array_map(
            fn($client) => [
                'id' => $client->getId(),
                'nom' => $client->getNom(),
                'prenom' => $client->getPrenom(),
                'telephone' => $client->getTelephone(),
            ],
            $clients
        ));
    }

    /**
     * Traite la création d'une commande au comptoir pour un client donné.
     */
    public function creer(): void
    {
        $this->exigerUtilisateurConnecte();

        $clientId = (int) ($_POST['client_id'] ?? 0);
        $statutInitial = StatutCommande::from($_POST['statut_initial'] ?? '');

        $lignes = [];
        foreach (($_POST['quantite'] ?? []) as $produitId => $quantite) {
            $quantite = (int) $quantite;
            if ($quantite > 0) {
                $lignes[(int) $produitId] = $quantite;
            }
        }

        try {
            $commande = $this->commandeService->creerCommandeSurPlace($clientId, $lignes, $statutInitial);

            Response::redirect("/gerant/commandes/{$commande->getId()}");
        } catch (CommandeInvalideException|ProduitInexistantException|StockInsuffisantException $exception) {
            $this->afficherVueInterne('gerant/commandes/nouvelle', [
                'produits' => $this->produitService->listerProduits(),
                'erreur' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * Affiche le détail d'une commande, avec ses lignes.
     *
     * @param string $id Identifiant de la commande, extrait de l'URL par le Router
     */
    public function detail(string $id): void
    {
        $this->exigerUtilisateurConnecte();

        $commande = $this->commandeService->consulterCommande((int) $id);

        $this->afficherVueInterne('gerant/commandes/detail', [
            'commande' => $commande,
            'client' => $commande !== null ? $this->clientService->consulterClient($commande->getClientId()) : null,
            'lignesEnrichies' => $commande !== null ? $this->enrichirLignes($commande) : [],
        ]);
    }

    /**
     * Traite le changement de statut d'une commande.
     *
     * @param string $id Identifiant de la commande, extrait de l'URL par le Router
     */
    public function changerStatut(string $id): void
    {
        $this->exigerUtilisateurConnecte();

        try {
            $this->commandeService->changerStatut(
                (int) $id,
                StatutCommande::from($_POST['statut'] ?? '')
            );

            Response::redirect("/gerant/commandes/{$id}");
        } catch (TransitionStatutInvalideException $exception) {
            $commande = $this->commandeService->consulterCommande((int) $id);

            $this->afficherVueInterne('gerant/commandes/detail', [
                'commande' => $commande,
                'client' => $commande !== null ? $this->clientService->consulterClient($commande->getClientId()) : null,
                'lignesEnrichies' => $commande !== null ? $this->enrichirLignes($commande) : [],
                'erreur' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * Annule une commande, quel que soit son statut courant. Le stock des
     * produits concernés est restitué automatiquement par le service.
     *
     * @param string $id Identifiant de la commande, extrait de l'URL par le Router
     */
    public function annuler(string $id): void
    {
        $this->exigerUtilisateurConnecte();

        $this->commandeService->changerStatut((int) $id, StatutCommande::ANNULEE);

        Response::redirect("/gerant/commandes/{$id}");
    }

    /**
     * Associe à chaque ligne de la commande le produit correspondant, pour
     * que la vue n'ait pas à faire de requêtes supplémentaires.
     *
     * @return array<int, array{ligne: \App\Models\LigneCommande, produit: ?\App\Models\Produit}>
     */
    private function enrichirLignes(\App\Models\Commande $commande): array
    {
        return array_map(
            fn($ligne) => [
                'ligne' => $ligne,
                'produit' => $this->produitService->consulterProduit($ligne->getProduitId()),
            ],
            $commande->getLignes()
        );
    }
}