<?php

declare(strict_types=1);

namespace App\Repositories;

/*
 * Contrat pour les repositories dont l'entité n'est jamais créée ni
 * modifiée par ce module — même logique que LectureSeuleRepository côté
 * Java Console. Aucun repository PHP n'utilise cette interface pour
 * l'instant (Client est pleinement géré ici, contrairement au Java), mais
 * elle reste disponible si un futur besoin de lecture seule apparaît.
 */
interface LectureSeuleRepositoryInterface
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
}