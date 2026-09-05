<?php

declare(strict_types=1);

namespace App\Models;

/*
 * Objet de transfert simple regroupant les indicateurs du tableau de bord.
 * Ne contient aucune logique : CommandeService::calculerStatistiques() la
 * construit, StatistiqueController n'a qu'à en lire les propriétés pour
 * les afficher. Pas de table ni de persistance associée — ce n'est pas
 * une entité, juste un résultat de calcul à la volée.
 */
final class Statistiques
{
    /**
     * @param float    $chiffreAffairesJour    Chiffre d'affaires du jour courant
     * @param float    $chiffreAffairesSemaine Chiffre d'affaires des 7 derniers jours
     * @param float    $chiffreAffairesMois    Chiffre d'affaires du mois courant
     * @param int      $nombreCommandes        Nombre total de commandes enregistrées
     * @param int      $commandesEnCours       Nombre de commandes non encore retirées ni annulées
     * @param string   $produitLePlusVendu     Libellé du produit ayant vendu le plus d'unités
     * @param string[] $top3Produits           Libellés des trois produits les plus vendus, avec leur quantité
     */
    public function __construct(
        public readonly float $chiffreAffairesJour,
        public readonly float $chiffreAffairesSemaine,
        public readonly float $chiffreAffairesMois,
        public readonly int $nombreCommandes,
        public readonly int $commandesEnCours,
        public readonly string $produitLePlusVendu,
        public readonly array $top3Produits,
    ) {
    }
}