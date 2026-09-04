<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/*
 * Levée lorsqu'une inscription (client) ou une création de compte
 * (utilisateur interne) est tentée avec un email déjà présent en base,
 * l'unicité de l'email étant une règle métier obligatoire.
 */
final class EmailDejaUtiliseException extends RuntimeException
{
}