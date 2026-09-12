<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TypePaiementRecu;
use DateTimeImmutable;

/*
 * Représente le reçu détaillé d'un paiement, généré automatiquement après
 * chaque enregistrement de paiement, qu'il solde totalement la commande ou
 * non. Relation 1-1 stricte avec Paiement : paiementId est contraint
 * UNIQUE en base.
 */
final class Recu
{
    public function __construct(
        private int $id,
        private string $numeroRecu,
        private int $paiementId,
        private TypePaiementRecu $typePaiement,
        private float $montant,
        private DateTimeImmutable $dateEmission,
    ) {
    }

    /**
     * Identifiant unique du reçu en base.
     *
     * @return int Identifiant du reçu
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Référence lisible du reçu (ex: REC-2026-000088), générée par le
     * service lors de son émission.
     *
     * @return string Numéro de reçu
     */
    public function getNumeroRecu(): string
    {
        return $this->numeroRecu;
    }

    /**
     * Identifiant du paiement associé à ce reçu.
     *
     * @return int Identifiant du paiement
     */
    public function getPaiementId(): int
    {
        return $this->paiementId;
    }

    /**
     * Nature du paiement au moment de l'émission : PARTIEL si un solde
     * restait dû après ce paiement, TOTAL si la commande était alors
     * intégralement réglée.
     *
     * @return TypePaiementRecu Type du paiement
     */
    public function getTypePaiement(): TypePaiementRecu
    {
        return $this->typePaiement;
    }

    /**
     * Montant réglé par le paiement associé à ce reçu.
     *
     * @return float Montant du paiement
     */
    public function getMontant(): float
    {
        return $this->montant;
    }

    /**
     * Date et heure d'émission du reçu.
     *
     * @return DateTimeImmutable Date d'émission
     */
    public function getDateEmission(): DateTimeImmutable
    {
        return $this->dateEmission;
    }

    /**
     * Reconstruit une instance de Recu à partir d'une ligne de résultat
     * PDO.
     *
     * @param array $ligne Ligne issue de la table recus
     * @return self Instance correspondant à cette ligne
     */
    public static function depuisLigne(array $ligne): self
    {
        return new self(
            id: (int) $ligne['id'],
            numeroRecu: $ligne['numero_recu'],
            paiementId: (int) $ligne['paiement_id'],
            typePaiement: TypePaiementRecu::from($ligne['type_paiement']),
            montant: (float) $ligne['montant'],
            dateEmission: new DateTimeImmutable($ligne['date_emission']),
        );
    }
}