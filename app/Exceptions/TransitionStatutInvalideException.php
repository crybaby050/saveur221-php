<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/*
 * Levée lorsqu'un changement de statut de commande ne respecte pas
 * l'enchaînement autorisé (EN_ATTENTE -> EN_PREPARATION -> PRETE ->
 * RETIREE), en dehors du cas particulier de l'annulation.
 */
final class TransitionStatutInvalideException extends RuntimeException
{
}