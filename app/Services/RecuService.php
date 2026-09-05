<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\TypePaiementRecu;
use App\Models\Paiement;
use App\Models\Recu;
use App\Repositories\RecuRepository;
use DateTimeImmutable;

/*
 * Génère et consulte les reçus. Un reçu est toujours créé automatiquement
 * par PaiementService juste après l'enregistrement d'un paiement, qu'il
 * solde totalement la commande ou non.
 */
final class RecuService
{
    public function __construct(
        private readonly RecuRepository $recuRepository,
    ) {
    }

    /**
     * Génère le reçu correspondant à un paiement, avec un numéro lisible
     * unique. Le type (PARTIEL ou TOTAL) est déterminé en amont par
     * PaiementService, seul à connaître le solde de la commande au moment
     * du paiement.
     *
     * @param Paiement         $paiement     Paiement concerné
     * @param TypePaiementRecu $typePaiement Nature du paiement à cet instant
     * @return Recu Le reçu créé
     */
    public function genererRecu(Paiement $paiement, TypePaiementRecu $typePaiement): Recu
    {
        $numero = $this->genererNumeroRecu();

        $recu = new Recu(
            0,
            $numero,
            $paiement->getId(),
            $typePaiement,
            $paiement->getMontant(),
            new DateTimeImmutable()
        );

        return $this->recuRepository->creer($recu);
    }

    /**
     * Retourne tous les reçus liés à une commande, dans l'ordre
     * chronologique d'émission.
     *
     * @param int $commandeId Identifiant de la commande recherchée
     * @return Recu[] Reçus de cette commande
     */
    public function consulterParCommande(int $commandeId): array
    {
        return $this->recuRepository->trouverParCommande($commandeId);
    }

    /**
     * Récupère le reçu associé à un paiement donné.
     *
     * @param int $paiementId Identifiant du paiement recherché
     * @return Recu|null Le reçu trouvé, ou null si le paiement n'en a pas
     */
    public function consulterParPaiement(int $paiementId): ?Recu
    {
        return $this->recuRepository->trouverParPaiement($paiementId);
    }

    /**
     * Retourne tous les reçus émis.
     *
     * @return Recu[] Liste de tous les reçus
     */
    public function listerRecus(): array
    {
        return $this->recuRepository->trouverTous();
    }

    /**
     * Génère un numéro de reçu lisible du type REC-2026-000088, sur le
     * même principe que FactureService::genererNumeroFacture().
     *
     * @return string Numéro de reçu généré
     */
    private function genererNumeroRecu(): string
    {
        $nombreRecus = count($this->recuRepository->trouverTous());
        $annee = (new DateTimeImmutable())->format('Y');

        return sprintf('REC-%s-%06d', $annee, $nombreRecus + 1);
    }
}