<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/*
 * Levée lorsqu'un client ou un utilisateur interne tente d'accéder à une
 * ressource qui ne lui appartient pas ou à laquelle son rôle ne donne pas
 * droit — par exemple un client consultant la commande d'un autre client,
 * ou un Gérant tentant d'ouvrir l'espace réservé à l'Administrateur.
 */
final class AccesRefuseException extends RuntimeException
{
}