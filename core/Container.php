<?php

declare(strict_types=1);

namespace Core;

use Closure;
use InvalidArgumentException;

/**
 * Container
 *
 * Petit conteneur d'injection de dépendances : permet d'enregistrer
 * des "fabriques" (closures) pour construire des services (repositories,
 * controllers...) et de les résoudre à la demande, en évitant les
 * `new` disséminés dans tout le code.
 */
final class Container
{
    /** @var array<string, Closure> Fabriques enregistrées, indexées par nom de classe/interface */
    private array $bindings = [];

    /** @var array<string, object> Instances déjà résolues (cache) */
    private array $instances = [];

    /**
     * Enregistre une fabrique pour construire un service donné.
     *
     * @param string  $abstract Nom de la classe ou identifiant du service
     * @param Closure $factory  Fonction qui reçoit le container et retourne l'instance
     */
    public function bind(string $abstract, Closure $factory): void
    {
        $this->bindings[$abstract] = $factory;
    }

    /**
     * Résout (instancie) un service.
     * Si une fabrique a été enregistrée pour ce nom, elle est utilisée ;
     * sinon, on tente une instanciation directe (new $abstract()).
     * Chaque service n'est construit qu'une seule fois (mise en cache).
     *
     * @throws InvalidArgumentException si la classe demandée n'existe pas
     */
    public function get(string $abstract): object
    {
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        if (isset($this->bindings[$abstract])) {
            $instance = ($this->bindings[$abstract])($this);
        } elseif (class_exists($abstract)) {
            $instance = new $abstract();
        } else {
            throw new InvalidArgumentException(
                "Impossible de résoudre le service « {$abstract} »."
            );
        }

        $this->instances[$abstract] = $instance;

        return $instance;
    }
}