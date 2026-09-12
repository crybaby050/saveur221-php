<?php

declare(strict_types=1);

namespace Core;

use PDO;
use PDOException;

/*
 * Database
 *
 * Point d'accès unique à la connexion PDO PostgreSQL, construite à partir
 * des variables lues par Env. Même rôle que DatabaseConnection.java côté
 * Java Console : une seule connexion réutilisée pour toute la durée de la
 * requête HTTP, plutôt qu'une reconnexion à chaque repository.
 */
final class Database
{
    private static ?PDO $connexion = null;

    /*
     * Constructeur privé : cette classe n'a pas vocation à être instanciée,
     * seule sa méthode statique getConnexion() est utilisée — même logique
     * que DatabaseConnection en Java.
     */
    private function __construct()
    {
    }

    public static function getConnexion(): PDO
    {
        if (self::$connexion !== null) {
            return self::$connexion;
        }

        $hote = Env::get('DB_HOST', 'localhost');
        $port = Env::get('DB_PORT', '5432');
        $nomBase = Env::get('DB_NAME');
        $utilisateur = Env::get('DB_USER');
        $motDePasse = Env::get('DB_PASSWORD', '');

        $dsn = "pgsql:host={$hote};port={$port};dbname={$nomBase}";

        try {
            self::$connexion = new PDO($dsn, $utilisateur, $motDePasse, [
                /*
                 * Les erreurs SQL doivent remonter sous forme d'exceptions
                 * (PDOException), jamais de faux-positifs silencieux — un
                 * repository ne doit jamais avoir à vérifier lui-même le
                 * code de retour d'une requête.
                 */
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,

                /*
                 * Les lignes de résultat sont indexées par nom de colonne
                 * uniquement (pas de doublon numérique), pour un mapping
                 * plus lisible dans les Repositories.
                 */
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,

                /*
                 * Les paramètres liés (requêtes préparées) conservent leur
                 * type natif plutôt que d'être castés en chaîne — nécessaire
                 * notamment pour que les booléens PostgreSQL soient transmis
                 * correctement.
                 */
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            throw new \RuntimeException(
                "Impossible de se connecter à la base de données PostgreSQL : " . $e->getMessage(),
                previous: $e
            );
        }

        return self::$connexion;
    }
}