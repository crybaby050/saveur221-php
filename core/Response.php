<?php

declare(strict_types=1);

namespace Core;

/*
 * Response
 *
 * Centralise l'émission des réponses HTTP, qu'elles soient destinées au
 * front web (redirection après un formulaire) ou à l'API JSON. Aucun
 * contrôleur ne doit appeler header()/echo directement : passer par cette
 * classe garantit un format cohérent (notamment pour les erreurs JSON) et
 * facilite un changement global de comportement si besoin plus tard.
 */
final class Response
{
    /*
     * Renvoie une réponse JSON avec le code de statut HTTP approprié —
     * utilisée par les contrôleurs de routes/api.php (ex: ajout au panier
     * en AJAX, sans rechargement de page).
     */
    public static function json(array $donnees, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($donnees, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /*
     * Redirige vers une autre URL du site et interrompt immédiatement
     * l'exécution — utilisée après le traitement d'un formulaire web
     * (ex: après connexion, retour vers le catalogue).
     */
    public static function redirect(string $chemin): never
    {
        header("Location: {$chemin}");
        exit;
    }

    /*
     * Fixe un code de statut HTTP sans produire de corps de réponse —
     * utilisée par le Router pour les cas d'erreur générique (404).
     */
    public static function status(int $code): void
    {
        http_response_code($code);
    }
}