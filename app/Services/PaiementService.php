<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\StatutPaiement;
use App\Enums\TypePaiementRecu;
use App\Exceptions\CommandeInexistanteException;
use App\Exceptions\MontantPaiementInvalideException;
use App\Models\Commande;
use App\Models\Paiement;
use App\Repositories\CommandeRepository;
use App\Repositories\PaiementRepository;
use DateTimeImmutable;

/*
 * Applique les règles métier liées aux paiements. Ne dépend pas
 * directement de FactureRepository : la seule dépendance transverse est
 * RecuService, chargé de générer systématiquement un reçu après chaque
 * paiement, qu'il solde totalement la commande ou non.
 */
final class PaiementService
{
    public function __construct(
        private readonly PaiementRepository $paiementRepository,
        private readonly CommandeRepository $commandeRepository,
        private readonly RecuService $recuService,
    ) {
    }

    /**
     * Retourne l'historique des paiements d'une commande.
     *
     * @param int $commandeId Identifiant de la commande recherchée
     * @return Paiement[] Paiements de cette commande
     */
    public function consulterParCommande(int $commandeId): array
    {
        return $this->paiementRepository->trouverParCommande($commandeId);
    }

    /**
     * Retourne les commandes non entièrement réglées.
     *
     * @return Commande[] Commandes impayées ou partiellement payées
     */
    public function consulterCommandesImpayees(): array
    {
        return $this->commandeRepository->trouverImpayeesOuPartielles();
    }

    /**
     * Enregistre un paiement pour une commande, après vérification que le
     * montant ne dépasse pas le solde restant. Met à jour le statut de
     * paiement de la commande en conséquence (PARTIEL ou PAYEE), puis
     * génère systématiquement le reçu correspondant.
     *
     * @param int   $commandeId Identifiant de la commande concernée
     * @param float $montant    Montant du paiement à enregistrer
     * @return Paiement Le paiement créé
     *
     * @throws CommandeInexistanteException si la commande n'existe pas
     * @throws MontantPaiementInvalideException si le montant dépasse le solde restant
     */
    public function enregistrerPaiement(int $commandeId, float $montant): Paiement
    {
        $commande = $this->commandeRepository->trouverParId($commandeId);

        if ($commande === null) {
            throw new CommandeInexistanteException("Commande introuvable avec l'id {$commandeId}.");
        }

        $totalDejaPaye = $this->paiementRepository->sommePaiements($commandeId);
        $montantRestant = $commande->getMontantTotal() - $totalDejaPaye;

        if ($montant > $montantRestant) {
            throw new MontantPaiementInvalideException(
                "Le montant saisi ({$montant}) dépasse le solde restant ({$montantRestant})."
            );
        }

        $paiement = new Paiement(0, $commandeId, $montant, new DateTimeImmutable());
        $paiement = $this->paiementRepository->creer($paiement);

        $nouveauTotalPaye = $totalDejaPaye + $montant;
        $soldeComplet = $nouveauTotalPaye >= $commande->getMontantTotal();

        $commande->changerStatutPaiement($soldeComplet ? StatutPaiement::PAYEE : StatutPaiement::PARTIEL);
        $this->commandeRepository->mettreAJour($commande);

        $typePaiement = $soldeComplet ? TypePaiementRecu::TOTAL : TypePaiementRecu::PARTIEL;
        $this->recuService->genererRecu($paiement, $typePaiement);

        return $paiement;
    }
}