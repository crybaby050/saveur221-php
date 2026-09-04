<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/*
 * Levée lorsqu'un client tente de déposer un avis sur une commande qui
 * n'est pas encore au statut RETIREE — un avis ne peut être laissé
 * qu'après le retrait effectif de la commande.
 */
final class AvisNonAutoriseException extends RuntimeException
{
}