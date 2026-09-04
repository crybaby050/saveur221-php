<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/*
 * Levée lorsqu'un produit recherché par son identifiant n'existe pas en
 * base — typiquement lors d'un ajout au panier ou d'une modification.
 */
final class ProduitInexistantException extends RuntimeException
{
}