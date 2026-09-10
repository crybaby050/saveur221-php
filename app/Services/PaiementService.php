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

final class PaiementService
{
    public function __construct(
        private readonly PaiementRepository $paiementRepository,
        private readonly CommandeRepository $commandeRepository,
        private readonly RecuService $recuService,
    ) {
    }

    /**
     * Retourne les paiements d'une commande.
     *
     * @param int $commandeId Identifiant de la commande
     * @return array Liste des paiements
     */
    public function consulterParCommande(int $commandeId): array
    {
        return $this->paiementRepository->trouverParCommande($commandeId);
    }

    /**
     * Retourne toutes les commandes avec leurs informations de paiement.
     *
     * @return array Liste des commandes et de leurs soldes
     */
    public function consulterToutesCommandesAvecPaiements(): array
    {
        $commandes = $this->commandeRepository->trouverTous();

        $resultat = [];

        foreach ($commandes as $commande) {
            $montantTotal = $commande->getMontantTotal();
            $montantPaye = $this->paiementRepository->sommePaiements($commande->getId());
            $montantRestant = max(0, $montantTotal - $montantPaye);

            if ($montantPaye <= 0) {
                $statutPaiement = 'IMPAYEE';
            } elseif ($montantRestant > 0) {
                $statutPaiement = 'PARTIELLE';
            } else {
                $statutPaiement = 'PAYEE';
            }

            $resultat[] = [
                'commande' => $commande,
                'montantTotal' => $montantTotal,
                'montantPaye' => $montantPaye,
                'montantRestant' => $montantRestant,
                'statutPaiement' => $statutPaiement,
            ];
        }

        return $resultat;
    }

    /**
     * Retourne une commande à partir de son identifiant.
     *
     * @param int $commandeId Identifiant de la commande recherchée
     * @return Commande|null La commande trouvée, ou null si elle n'existe pas
     */
    public function consulterCommande(int $commandeId): ?Commande
    {
        return $this->commandeRepository->trouverParId($commandeId);
    }

    /**
     * Retourne les commandes impayées ou partiellement payées.
     *
     * @return array Liste des commandes concernées
     */
    public function consulterCommandesImpayees(): array
    {
        return $this->commandeRepository->trouverImpayeesOuPartielles();
    }

    /**
     * Enregistre un paiement pour une commande.
     *
     * @param int $commandeId Identifiant de la commande
     * @param float $montant Montant du paiement
     * @return Paiement Paiement créé
     */
    public function enregistrerPaiement(int $commandeId, float $montant): Paiement
    {
        $commande = $this->commandeRepository->trouverParId($commandeId);

        if ($commande === null) {
            throw new CommandeInexistanteException(
                "Commande introuvable avec l'id {$commandeId}."
            );
        }

        if ($montant <= 0) {
            throw new MontantPaiementInvalideException(
                'Veuillez saisir un montant de paiement valide.'
            );
        }

        $totalDejaPaye = $this->paiementRepository->sommePaiements($commandeId);

        $montantRestant = $commande->getMontantTotal() - $totalDejaPaye;

        if ($montant > $montantRestant) {
            throw new MontantPaiementInvalideException(
                'Le montant saisi (' .
                number_format($montant, 0, ',', ' ') .
                ' FCFA) dépasse le solde restant (' .
                number_format($montantRestant, 0, ',', ' ') .
                ' FCFA).'
            );
        }

        $paiement = new Paiement(
            0,
            $commandeId,
            $montant,
            new DateTimeImmutable()
        );

        $paiement = $this->paiementRepository->creer($paiement);

        $nouveauTotalPaye = $totalDejaPaye + $montant;

        $soldeComplet = $nouveauTotalPaye >= $commande->getMontantTotal();

        $commande->changerStatutPaiement(
            $soldeComplet
                ? StatutPaiement::PAYEE
                : StatutPaiement::PARTIEL
        );

        $this->commandeRepository->mettreAJour($commande);

        $typePaiement = $soldeComplet
            ? TypePaiementRecu::TOTAL
            : TypePaiementRecu::PARTIEL;

        $this->recuService->genererRecu($paiement, $typePaiement);

        return $paiement;
    }
}