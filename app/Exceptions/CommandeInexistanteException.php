<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/*
 * Levée lorsqu'une commande recherchée par son identifiant ou son numéro
 * n'existe pas en base.
 */
final class CommandeInexistanteException extends RuntimeException
{
}