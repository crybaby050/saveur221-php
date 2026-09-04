<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/*
 * Levée lorsqu'une suppression de catégorie est tentée alors qu'elle
 * contient encore des produits — règle métier interdisant cette
 * suppression tant que la catégorie est utilisée.
 */
final class CategorieUtiliseeException extends RuntimeException
{
}