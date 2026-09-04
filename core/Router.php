<?php

declare(strict_types=1);

namespace Core;

/*
 * Router
 *
 * Fait correspondre une méthode HTTP + une URI à un contrôleur et une
 * action, avec extraction des paramètres dynamiques (ex: /produits/{id}).
 * Utilisé indifféremment par routes/web.php et routes/api.php : ces deux
 * fichiers ne font qu'enregistrer des routes sur la même instance — la
 * distinction HTML/JSON se joue plus tard, dans la réponse renvoyée par
 * chaque contrôleur via Core\Response, pas dans le routeur lui-même.
 */
final class Router
{
    /*
     * Chaque route enregistrée est stockée comme un tableau associatif
     * plutôt qu'une classe dédiée : la structure est simple et interne à
     * cette classe, une classe Route séparée ajouterait de l'indirection
     * sans bénéfice réel ici.
     */
    private array $routes = [];

    public function __construct(private readonly Container $container)
    {
    }

    public function get(string $chemin, array $action): void
    {
        $this->ajouterRoute('GET', $chemin, $action);
    }

    public function post(string $chemin, array $action): void
    {
        $this->ajouterRoute('POST', $chemin, $action);
    }

    private function ajouterRoute(string $methode, string $chemin, array $action): void
    {
        $this->routes[] = [
            'methode' => $methode,
            'chemin' => $chemin,
            'action' => $action,
        ];
    }

    /*
     * Convertit un chemin défini avec des accolades ({id}, {numero}) en
     * expression régulière, en nommant chaque groupe capturé — permet de
     * retrouver les paramètres par leur nom plutôt que par leur position,
     * plus lisible côté contrôleur.
     */
    private function compilerChemin(string $chemin): string
    {
        $motif = preg_replace('#\{([a-zA-Z_]+)\}#', '(?P<$1>[^/]+)', $chemin);

        return '#^' . $motif . '$#';
    }

    /*
     * Recherche la première route correspondant à la méthode et à l'URI
     * demandées, instancie le contrôleur via le Container (injection de
     * dépendances plutôt qu'un "new" en dur), puis appelle l'action avec
     * les paramètres extraits de l'URL.
     */
    public function dispatcher(string $methode, string $uri): void
    {
        $uri = parse_url($uri, PHP_URL_PATH) ?? '/';
        $uri = rtrim($uri, '/');
        $uri = $uri === '' ? '/' : $uri;

        foreach ($this->routes as $route) {
            if ($route['methode'] !== $methode) {
                continue;
            }

            $motif = $this->compilerChemin($route['chemin']);

            if (preg_match($motif, $uri, $correspondances) === 1) {
                $parametres = array_filter(
                    $correspondances,
                    fn(string $cle) => !is_int($cle),
                    ARRAY_FILTER_USE_KEY
                );

                [$classeControleur, $methodeAction] = $route['action'];
                $controleur = $this->container->get($classeControleur);

                $controleur->{$methodeAction}(...array_values($parametres));

                return;
            }
        }

        $this->routeIntrouvable($methode, $uri);
    }

    /*
     * Aucune route ne correspond : renvoie une 404 générique. Le format
     * (HTML ou JSON) est déduit du préfixe /api, seule concession que le
     * routeur fait à la distinction web/api, uniquement pour ce cas limite.
     */
    private function routeIntrouvable(string $methode, string $uri): void
    {
        http_response_code(404);

        if (str_starts_with($uri, '/api')) {
            Response::json(['erreur' => "Route introuvable : {$methode} {$uri}"], 404);
            return;
        }

        echo "Page introuvable : {$methode} {$uri}";
    }
}