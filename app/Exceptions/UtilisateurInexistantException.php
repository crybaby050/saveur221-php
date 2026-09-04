<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/*
 * Levée lorsqu'un utilisateur interne ou un client recherché par email ou
 * identifiant n'existe pas en base — typiquement lors d'une tentative de
 * connexion avec un email inconnu.
 */
final class UtilisateurInexistantException extends RuntimeException
{
}