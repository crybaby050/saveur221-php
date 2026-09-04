<?php

declare(strict_types=1);

namespace Core;

/*
 * Session
 *
 * Encapsule $_SESSION plutôt que de le manipuler directement dans les
 * services et contrôleurs — centralise le démarrage de session, la
 * régénération d'identifiant après connexion (protection contre la fixation
 * de session), et fournit une API nommée plus explicite qu'un accès par
 * clé de tableau brut.
 */
final class Session
{
    private static bool $demarree = false;

    /*
     * Démarre la session PHP si ce n'est pas déjà fait. Appelée une fois
     * au tout début du cycle de requête (public/index.php), mais rendue
     * idempotente ici par sécurité si jamais elle est sollicitée ailleurs
     * avant que la session native ne soit active.
     */
    public static function demarrer(): void
    {
        if (self::$demarree) {
            return;
        }

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        self::$demarree = true;
    }

    public static function set(string $cle, mixed $valeur): void
    {
        $_SESSION[$cle] = $valeur;
    }

    public static function get(string $cle, mixed $defaut = null): mixed
    {
        return $_SESSION[$cle] ?? $defaut;
    }

    public static function has(string $cle): bool
    {
        return isset($_SESSION[$cle]);
    }

    public static function remove(string $cle): void
    {
        unset($_SESSION[$cle]);
    }

    /*
     * Régénère l'identifiant de session tout en conservant ses données —
     * à appeler juste après une connexion réussie (client ou utilisateur
     * interne), pour empêcher qu'un identifiant de session obtenu avant
     * l'authentification ne reste valide après.
     */
    public static function regenerer(): void
    {
        session_regenerate_id(true);
    }

    /*
     * Détruit entièrement la session — utilisée à la déconnexion, plutôt
     * qu'un simple remove() clé par clé qui risquerait d'oublier une donnée
     * sensible ajoutée plus tard dans le projet.
     */
    public static function detruire(): void
    {
        $_SESSION = [];
        session_destroy();
        self::$demarree = false;
    }
}