<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/*
 * Levée lorsqu'un utilisateur interne dont le compte a été désactivé
 * tente de se connecter.
 */
final class CompteDesactiveException extends RuntimeException
{
}