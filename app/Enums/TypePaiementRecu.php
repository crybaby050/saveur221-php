<?php

declare(strict_types=1);

namespace App\Enums;

/*
 * Nature d'un paiement au moment où son reçu est émis : TOTAL si le solde
 * restant tombe à zéro après ce paiement, PARTIEL sinon. Déterminé une
 * seule fois à l'émission — un reçu ne change jamais de type après coup.
 */
enum TypePaiementRecu: string
{
    case PARTIEL = 'PARTIEL';
    case TOTAL = 'TOTAL';
}