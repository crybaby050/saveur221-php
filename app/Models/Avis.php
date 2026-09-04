<?php

declare(strict_types=1);

namespace App\Models;

use DateTimeImmutable;

/*
 * Représente l'avis déposé par un client sur une commande retirée. La
 * contrainte d'unicité (un seul avis par commande) est déjà garantie au
 * niveau base par une clé UNIQUE sur commandeId, en complément de la
 * vérification applicative faite par le service avant insertion.
 */
final class Avis
{
    public function __construct(
        private int $id,
        private int $commandeId,
        private int $clientId,
        private int $note,
        private ?string $commentaire,
        private DateTimeImmutable $dateAvis,
    ) {
    }

    /**
     * Identifiant unique de l'avis en base.
     *
     * @return int Identifiant de l'avis
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Identifiant de la commande sur laquelle porte cet avis. Un avis ne
     * peut être déposé que sur une commande au statut RETIREE, règle
     * vérifiée par le service, pas ici.
     *
     * @return int Identifiant de la commande concernée
     */
    public function getCommandeId(): int
    {
        return $this->commandeId;
    }

    /**
     * Identifiant du client ayant déposé l'avis.
     *
     * @return int Identifiant du client
     */
    public function getClientId(): int
    {
        return $this->clientId;
    }

    /**
     * Note attribuée par le client, comprise entre 1 et 5. La validité de
     * cette plage est vérifiée par le service avant construction, pas ici.
     *
     * @return int Note sur 5
     */
    public function getNote(): int
    {
        return $this->note;
    }

    /**
     * Commentaire libre laissé par le client, facultatif.
     *
     * @return string|null Commentaire du client, ou null si aucun n'a été saisi
     */
    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    /**
     * Date et heure à laquelle l'avis a été déposé.
     *
     * @return DateTimeImmutable Date de dépôt de l'avis
     */
    public function getDateAvis(): DateTimeImmutable
    {
        return $this->dateAvis;
    }

    /**
     * Reconstruit une instance d'Avis à partir d'une ligne de résultat PDO.
     *
     * @param array $ligne Ligne issue de la table avis
     * @return self Instance correspondant à cette ligne
     */
    public static function depuisLigne(array $ligne): self
    {
        return new self(
            id: (int) $ligne['id'],
            commandeId: (int) $ligne['commande_id'],
            clientId: (int) $ligne['client_id'],
            note: (int) $ligne['note'],
            commentaire: $ligne['commentaire'],
            dateAvis: new DateTimeImmutable($ligne['date_avis']),
        );
    }
}