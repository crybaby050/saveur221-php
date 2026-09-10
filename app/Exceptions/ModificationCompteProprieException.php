<?php

declare(strict_types=1);

namespace App\Exceptions;

/*
 * Levée lorsqu'un administrateur tente une action sur son propre compte
 * qui pourrait le priver de ses droits (changement de son propre rôle,
 * désactivation ou suppression de son propre compte).
 */
final class ModificationCompteProprieException extends \RuntimeException
{
}