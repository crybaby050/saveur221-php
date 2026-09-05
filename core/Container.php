<?php

declare(strict_types=1);

namespace Core;

use Closure;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionParameter;

/**
 * Container
 *
 * Petit conteneur d'injection de dépendances : permet d'enregistrer
 * des "fabriques" (closures) pour construire des services (repositories,
 * controllers...) et de les résoudre à la demande, en évitant les
 * `new` disséminés dans tout le code.
 *
 * En l'absence de fabrique enregistrée pour une classe donnée, get()
 * tente une résolution automatique par réflexion : elle inspecte le
 * constructeur de la classe, et pour chaque paramètre typé avec une autre
 * classe, résout récursivement cette dépendance de la même façon — sans
 * qu'un bind() explicite soit nécessaire, tant que la classe ne dépend
 * que de classes concrètes (pas d'interfaces, qui nécessitent de préciser
 * explicitement quelle implémentation utiliser via bind()).
 */
final class Container
{
    /** @var array<string, Closure> Fabriques enregistrées, indexées par nom de classe/interface */
    private array $bindings = [];

    /** @var array<string, object> Instances déjà résolues (cache) */
    private array $instances = [];

    /**
     * Enregistre une fabrique pour construire un service donné. Reste
     * utile pour les cas que la résolution automatique ne peut pas
     * deviner seule : une dépendance typée par une interface plutôt
     * qu'une classe concrète, ou une construction nécessitant des
     * arguments qui ne sont pas eux-mêmes des services (valeurs de
     * configuration, par exemple).
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
     * sinon, la classe est construite automatiquement par réflexion.
     * Chaque service n'est construit qu'une seule fois (mise en cache).
     *
     * @throws InvalidArgumentException si la classe demandée n'existe pas
     *                                   ou si l'une de ses dépendances ne
     *                                   peut pas être résolue
     */
    public function get(string $abstract): object
    {
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        if (isset($this->bindings[$abstract])) {
            $instance = ($this->bindings[$abstract])($this);
        } else {
            $instance = $this->construireAutomatiquement($abstract);
        }

        $this->instances[$abstract] = $instance;

        return $instance;
    }

    /**
     * Construit une instance en inspectant son constructeur par réflexion.
     * Pour chaque paramètre typé avec une classe (pas un type primitif
     * comme string ou int), résout récursivement cette dépendance via
     * get() — permettant de câbler des chaînes de dépendances entières
     * (Controller -> Service -> Repository) sans aucune configuration
     * manuelle, tant qu'aucune interface n'intervient dans la chaîne.
     *
     * @param string $abstract Nom de la classe à construire
     * @return object Instance construite, avec toutes ses dépendances injectées
     *
     * @throws InvalidArgumentException si la classe n'existe pas, ou si un
     *                                   paramètre du constructeur ne peut
     *                                   pas être résolu automatiquement
     */
    private function construireAutomatiquement(string $abstract): object
    {
        if (!class_exists($abstract)) {
            throw new InvalidArgumentException(
                "Impossible de résoudre le service « {$abstract} »."
            );
        }

        $reflexion = new ReflectionClass($abstract);
        $constructeur = $reflexion->getConstructor();

        if ($constructeur === null) {
            return new $abstract();
        }

        $arguments = array_map(
            fn(ReflectionParameter $parametre) => $this->resoudreParametre($parametre),
            $constructeur->getParameters()
        );

        return $reflexion->newInstanceArgs($arguments);
    }

    /**
     * Résout un unique paramètre de constructeur : si son type est une
     * classe, la demande récursivement au container ; si le paramètre a
     * une valeur par défaut (utile pour un type primitif comme string ou
     * int), l'utilise à la place.
     *
     * @param ReflectionParameter $parametre Paramètre du constructeur à résoudre
     * @return mixed Valeur à transmettre pour ce paramètre
     *
     * @throws InvalidArgumentException si le paramètre ne peut être résolu
     *                                   ni par injection, ni par valeur par défaut
     */
    private function resoudreParametre(ReflectionParameter $parametre): mixed
    {
        $type = $parametre->getType();

        if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
            return $this->get($type->getName());
        }

        if ($parametre->isDefaultValueAvailable()) {
            return $parametre->getDefaultValue();
        }

        throw new InvalidArgumentException(
            "Impossible de résoudre le paramètre « {$parametre->getName()} » " .
            "du constructeur de « {$parametre->getDeclaringClass()?->getName()} »."
        );
    }
}