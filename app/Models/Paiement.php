<?php

declare(strict_types=1);

namespace App\Models;

use DateTimeImmutable;

/*
 * Représente un paiement enregistré pour une commande. Une commande peut
 * recevoir plusieurs paiements successifs (règlement en plusieurs fois),
 * chacun générant systématiquement son propre Recu.
 */
final class Paiement
{
    public function __construct(
        private int $id,
        private int $commandeId,
        private float $montant,
        private DateTimeImmutable $datePaiement,
    ) {
    }

    /**
     * Identifiant unique du paiement en base.
     *
     * @return int Identifiant du paiement
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Identifiant de la commande à laquelle ce paiement se rapporte.
     *
     * @return int Identifiant de la commande
     */
    public function getCommandeId(): int
    {
        return $this->commandeId;
    }

    /**
     * Montant réglé par ce paiement. La vérification qu'il ne dépasse pas
     * le solde restant est effectuée par le service, pas ici.
     *
     * @return float Montant du paiement
     */
    public function getMontant(): float
    {
        return $this->montant;
    }

    /**
     * Date et heure à laquelle le paiement a été enregistré.
     *
     * @return DateTimeImmutable Date du paiement
     */
    public function getDatePaiement(): DateTimeImmutable
    {
        return $this->datePaiement;
    }

    /**
     * Reconstruit une instance de Paiement à partir d'une ligne de
     * résultat PDO.
     *
     * @param array $ligne Ligne issue de la table paiements
     * @return self Instance correspondant à cette ligne
     */
    public static function depuisLigne(array $ligne): self
    {
        return new self(
            id: (int) $ligne['id'],
            commandeId: (int) $ligne['commande_id'],
            montant: (float) $ligne['montant'],
            datePaiement: new DateTimeImmutable($ligne['date_paiement']),
        );
    }
}