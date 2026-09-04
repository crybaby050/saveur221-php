<?php

declare(strict_types=1);

namespace App\Models;

use DateTimeImmutable;

/*
 * Représente la facture détaillée d'une commande, générée automatiquement
 * dès sa création. Relation 1-1 stricte avec Commande : commandeId est
 * contraint UNIQUE en base.
 */
final class Facture
{
    public function __construct(
        private int $id,
        private string $numeroFacture,
        private int $commandeId,
        private float $montantTotal,
        private DateTimeImmutable $dateEmission,
    ) {
    }

    /**
     * Identifiant unique de la facture en base.
     *
     * @return int Identifiant de la facture
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Référence lisible de la facture (ex: FAC-2026-000104), générée par
     * le service lors de son émission.
     *
     * @return string Numéro de facture
     */
    public function getNumeroFacture(): string
    {
        return $this->numeroFacture;
    }

    /**
     * Identifiant de la commande facturée.
     *
     * @return int Identifiant de la commande
     */
    public function getCommandeId(): int
    {
        return $this->commandeId;
    }

    /**
     * Montant total de la commande, figé au moment de l'émission de la
     * facture.
     *
     * @return float Montant total facturé
     */
    public function getMontantTotal(): float
    {
        return $this->montantTotal;
    }

    /**
     * Date et heure d'émission de la facture.
     *
     * @return DateTimeImmutable Date d'émission
     */
    public function getDateEmission(): DateTimeImmutable
    {
        return $this->dateEmission;
    }

    /**
     * Reconstruit une instance de Facture à partir d'une ligne de résultat
     * PDO.
     *
     * @param array $ligne Ligne issue de la table factures
     * @return self Instance correspondant à cette ligne
     */
    public static function depuisLigne(array $ligne): self
    {
        return new self(
            id: (int) $ligne['id'],
            numeroFacture: $ligne['numero_facture'],
            commandeId: (int) $ligne['commande_id'],
            montantTotal: (float) $ligne['montant_total'],
            dateEmission: new DateTimeImmutable($ligne['date_emission']),
        );
    }
}