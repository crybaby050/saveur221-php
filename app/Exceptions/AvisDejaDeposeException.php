<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/*
 * Levée lorsqu'un client tente de déposer un second avis sur une commande
 * qui en possède déjà un — règle métier limitant à un seul avis par
 * commande.
 */
final class AvisDejaDeposeException extends RuntimeException
{
}