<?php

declare(strict_types=1);

namespace App\Repositories;

/*
 * Contrat commun à tous les repositories permettant la création, la
 * modification et la suppression de leur entité. Équivalent PHP de
 * Repository<T, ID> côté Java Console — les génériques n'existant pas
 * nativement en PHP, chaque implémentation précise ses types réels via
 * des annotations PHPDoc sur ses propres méthodes.
 */
interface RepositoryInterface
{
    /**
     * Recherche une entité par son identifiant.
     *
     * @param int $id Identifiant recherché
     * @return object|null L'entité trouvée, ou null si aucune ne correspond
     */
    public function trouverParId(int $id): ?object;

    /**
     * Retourne l'ensemble des entités de ce type.
     *
     * @return object[] Liste des entités
     */
    public function trouverTous(): array;

    /**
     * Insère une nouvelle entité en base.
     *
     * @param object $entite Entité à créer (id ignoré, généré par la base)
     * @return object L'entité créée, avec son identifiant généré renseigné
     */
    public function creer(object $entite): object;

    /**
     * Met à jour une entité déjà existante, identifiée par son id.
     *
     * @param object $entite Entité contenant les valeurs à jour
     */
    public function mettreAJour(object $entite): void;

    /**
     * Supprime une entité par son identifiant.
     *
     * @param int $id Identifiant de l'entité à supprimer
     */
    public function supprimerParId(int $id): void;
}