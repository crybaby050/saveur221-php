<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/*
 * Levée lorsqu'une commande ne respecte pas une condition minimale pour
 * être créée, par exemple un panier vide au moment de la validation.
 */
final class CommandeInvalideException extends RuntimeException
{
}