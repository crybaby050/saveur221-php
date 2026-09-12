<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/*
 * Levée lorsqu'un mot de passe saisi ne respecte pas la contrainte
 * métier de longueur minimale (6 caractères), à l'inscription ou à la
 * création d'un compte interne.
 */
final class MotDePasseInvalideException extends RuntimeException
{
}