<?php

declare(strict_types=1);

namespace Core;

/*
 * View
 *
 * Rendu de vues PHP classiques (pas de moteur de template dédié), avec deux
 * garanties : l'échappement systématique des données affichées via e(), et
 * l'insertion automatique du contenu d'une vue à l'intérieur d'un layout
 * commun — reprend le même rôle que views/ côté Java, mais pour du HTML.
 */
final class View
{
    private const DOSSIER_VUES = __DIR__ . '/../resources/views/';

    /*
     * Affiche une vue, éventuellement enveloppée dans un layout.
     *
     * $vue et $layout sont des chemins relatifs à resources/views/, sans
     * extension (ex: 'produits/catalogue', 'layout/base.layout').
     * $donnees est extrait en variables locales accessibles dans le fichier
     * de vue — EXTR_SKIP évite qu'une clé nommée "vue" ou "layout" n'écrase
     * accidentellement les paramètres de cette méthode.
     */
    public static function render(string $vue, array $donnees = [], ?string $layout = 'layout/base.layout'): void
    {
        $contenu = self::rendreFichier($vue, $donnees);

        if ($layout === null) {
            echo $contenu;
            return;
        }

        echo self::rendreFichier($layout, [...$donnees, 'contenu' => $contenu]);
    }

    /*
     * Charge un fichier de vue dans un tampon de sortie plutôt que de
     * l'échoer directement — nécessaire pour pouvoir ensuite injecter ce
     * contenu dans le layout via la variable $contenu.
     */
    private static function rendreFichier(string $vue, array $donnees): string
    {
        $cheminFichier = self::DOSSIER_VUES . $vue . '.php';

        if (!is_file($cheminFichier)) {
            throw new \RuntimeException("Vue introuvable : {$vue} (attendue dans resources/views/{$vue}.php)");
        }

        extract($donnees, EXTR_SKIP);

        ob_start();
        require $cheminFichier;

        return ob_get_clean();
    }

    /*
     * Échappe une valeur avant affichage HTML — à utiliser systématiquement
     * dans les fichiers de vue autour de toute donnée provenant de la base
     * ou d'une saisie utilisateur, pour se prémunir des failles XSS.
     * Convention d'usage dans les vues : <?= View::e($produit->libelle) ?>
     */
    public static function e(?string $valeur): string
    {
        return htmlspecialchars($valeur ?? '', ENT_QUOTES, 'UTF-8');
    }
}