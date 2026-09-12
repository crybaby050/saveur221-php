<?php

declare(strict_types=1);

use Core\Container;
use Core\Database;
use Core\Env;
use Core\Router;
use Core\Session;

require __DIR__ . '/../vendor/autoload.php';

/*
 * Point d'entrée unique de l'application (front controller) : toute
 * requête HTTP passe par ce fichier, qui charge la configuration, ouvre
 * la session, construit le Container et le Router, puis délègue le
 * traitement au contrôleur approprié. Aucune autre logique métier ne doit
 * vivre ici.
 */

Env::load(__DIR__ . '/../.env');
Session::demarrer();

/*
 * Une seule instance de Container pour toute la durée de la requête :
 * chaque service n'est donc construit qu'une fois, même s'il est demandé
 * par plusieurs contrôleurs différents au cours du dispatch.
 */
$container = new Container();
$router = new Router($container);

require __DIR__ . '/../routes/web.php';
require __DIR__ . '/../routes/web-interne.php';
require __DIR__ . '/../routes/api.php';

try {
    $router->dispatcher($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
} catch (\App\Exceptions\AccesRefuseException $exception) {
    http_response_code(403);
    echo "Accès refusé : " . htmlspecialchars($exception->getMessage(), ENT_QUOTES, 'UTF-8');
} catch (\Throwable $exception) {
    /*
     * Filet de sécurité final : toute exception non interceptée par un
     * contrôleur (erreur de programmation, panne de connexion à la base,
     * etc.) est capturée ici plutôt que d'afficher une trace brute à
     * l'utilisateur. En développement, le détail reste affiché pour
     * faciliter le débogage ; en production, on masquerait ce message.
     */
    http_response_code(500);

    if (Env::get('APP_ENV') === 'development') {
        echo "Erreur serveur : " . htmlspecialchars($exception->getMessage(), ENT_QUOTES, 'UTF-8');
    } else {
        echo "Une erreur est survenue. Veuillez réessayer plus tard.";
    }
}