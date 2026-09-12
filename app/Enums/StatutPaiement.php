<?php

declare(strict_types=1);

namespace App\Enums;

/*
 * Statut de paiement d'une commande, indépendant de son statut de
 * préparation. Une commande naît toujours à IMPAYE ; sa progression vers
 * PARTIEL puis PAYEE est pilotée par le service de paiement, jamais
 * modifiée manuellement.
 */
enum StatutPaiement: string
{
    case IMPAYE = 'IMPAYE';
    case PARTIEL = 'PARTIEL';
    case PAYEE = 'PAYEE';
}