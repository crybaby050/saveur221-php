<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/*
 * Levée lorsqu'un paiement enregistré dépasserait le solde restant à
 * payer sur la commande concernée.
 */
final class MontantPaiementInvalideException extends RuntimeException
{
}