<?php

declare(strict_types=1);

namespace App\Enums;

/*
 * Rôle attribué à un utilisateur interne (personnel du restaurant).
 * Correspond strictement à la colonne "nom" de la table roles — même
 * contrat que l'enum Role côté Java Console.
 */
enum Role: string
{
    case ADMIN = 'ADMIN';
    case GERANT = 'GERANT';
}