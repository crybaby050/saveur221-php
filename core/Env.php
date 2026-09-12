<?php

declare(strict_types=1);

namespace Core;

/*
 * Env
 *
 * Charge les variables du fichier .env dans l'environnement du processus
 * PHP. Volontairement écrite à la main plutôt que d'ajouter une dépendance
 * externe : le format .env attendu ici reste simple (clé=valeur, une par
 * ligne, commentaires avec #), ce qui ne justifie pas une bibliothèque.
 */
final class Env
{
    private static bool $loaded = false;

    /*
     * Lit le fichier .env indiqué et place chaque variable dans $_ENV ainsi
     * que dans l'environnement du processus (getenv/putenv), pour que le
     * reste de l'application puisse la lire indifféremment via l'une ou
     * l'autre API. N'a d'effet qu'une seule fois par requête (idempotent),
     * pour éviter de reparser le fichier à chaque appel de get().
     */
    public static function load(string $cheminFichier): void
    {
        if (self::$loaded) {
            return;
        }

        if (!is_file($cheminFichier)) {
            throw new \RuntimeException(
                "Fichier d'environnement introuvable : {$cheminFichier}. " .
                "Copie .env.example vers .env et renseigne tes propres valeurs."
            );
        }

        $lignes = file($cheminFichier, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lignes as $ligne) {
            $ligne = trim($ligne);

            if ($ligne === '' || str_starts_with($ligne, '#')) {
                continue;
            }

            [$cle, $valeur] = array_pad(explode('=', $ligne, 2), 2, '');
            $cle = trim($cle);
            $valeur = trim($valeur, " \t\n\r\0\x0B\"'");

            $_ENV[$cle] = $valeur;
            putenv("{$cle}={$valeur}");
        }

        self::$loaded = true;
    }

    /*
     * Récupère une variable d'environnement, avec une valeur par défaut si
     * elle est absente — évite un accès direct à $_ENV dispersé partout
     * dans le code, et centralise la logique de repli.
     */
    public static function get(string $cle, ?string $defaut = null): ?string
    {
        return $_ENV[$cle] ?? $defaut;
    }
}