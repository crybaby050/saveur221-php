<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/*
 * Levée lorsqu'une colorimétrie choisie pour une catégorie est déjà
 * attribuée à une autre catégorie — chaque couleur ne peut identifier
 * qu'une seule catégorie à la fois dans l'interface.
 */
final class CouleurDejaUtiliseeException extends RuntimeException
{
}