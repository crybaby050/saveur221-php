<?php

declare(strict_types=1);

namespace App\Enums;

/*
 * Statut d'une commande, reflétant son avancement dans le cycle de
 * préparation. Correspond strictement au type PostgreSQL statut_commande.
 * Transition normale : EN_ATTENTE -> EN_PREPARATION -> PRETE -> RETIREE.
 * ANNULEE est atteignable depuis n'importe quel statut précédent — règle
 * vérifiée en couche service, pas ici.
 */
enum StatutCommande: string
{
    case EN_ATTENTE = 'EN_ATTENTE';
    case EN_PREPARATION = 'EN_PREPARATION';
    case PRETE = 'PRETE';
    case RETIREE = 'RETIREE';
    case ANNULEE = 'ANNULEE';
}