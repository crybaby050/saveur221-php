<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/*
 * Levée lorsque le mot de passe saisi ne correspond pas au hash stocké
 * pour le compte concerné, lors d'une tentative de connexion.
 */
final class MotDePasseIncorrectException extends RuntimeException
{
}