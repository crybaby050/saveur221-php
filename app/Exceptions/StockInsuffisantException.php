<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/*
 * Levée lorsqu'une quantité demandée (ajout au panier ou validation de
 * commande) dépasse le stock actuellement disponible pour ce produit.
 */
final class StockInsuffisantException extends RuntimeException
{
}