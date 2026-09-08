<?php

use Core\View;

$titrePage = 'Tableau de bord';
$section = 'dashboard';

$formaterMontant = static fn(float $montant): string => number_format($montant, 0, ',', ' ') . ' FCFA';

/*
 * Icônes des cartes du dashboard, propres à cette vue.
 */
function icone_stat(string $nom): string
{
    $icones = [
        'argent' => '<circle cx="12" cy="12" r="9"/><path d="M9.5 9.5c0-1.1 1.1-2 2.5-2s2.5.9 2.5 2c0 2-5 1.5-5 4 0 1.1 1.1 2 2.5 2s2.5-.9 2.5-2M12 6.5v1M12 16.5v1"/>',
        'calendrier' => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4" stroke-linecap="round"/>',
        'graphique' => '<path d="M4 20V10M10 20V4M16 20v-7M22 20H2" stroke-linecap="round" stroke-linejoin="round"/>',
        'commandes' => '<path d="M6 3h12l1 5H5l1-5Z"/><path d="M5 8v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V8"/><path d="M9 12h6" stroke-linecap="round"/>',
        'sablier' => '<path d="M6 3h12M6 21h12M7 3v4a5 5 0 0 0 5 5 5 5 0 0 0 5-5V3M7 21v-4a5 5 0 0 1 5-5 5 5 0 0 1 5 5v4" stroke-linecap="round" stroke-linejoin="round"/>',
        'trophee' => '<path d="M8 4h8v5a4 4 0 0 1-8 0V4Z"/><path d="M8 5H5a3 3 0 0 0 3 3M16 5h3a3 3 0 0 1-3 3"/><path d="M12 13v3M9 20h6M10 16.5h4v3.5h-4z"/>',
        'classement' => '<path d="M4 21V10M12 21V4M20 21v-7" stroke-linecap="round"/>',
    ];

    return $icones[$nom] ?? '';
}

/*
 * Chaque carte reçoit sa propre couleur d'accent (badge icône + petit
 * indicateur de tendance) plutôt qu'une seule couleur uniforme partout —
 * facilite le repérage visuel rapide d'une carte à l'autre.
 */
$carteStat = static function (string $icone, string $label, string $valeur, string $couleur) {
    $classesBadge = match ($couleur) {
        'violet' => 'bg-bordeaux/10 text-bordeaux',
        'info' => 'bg-info/10 text-info',
        'succes' => 'bg-succes/10 text-succes',
        'alerte' => 'bg-alerte/10 text-alerte',
        default => 'bg-bordeaux/10 text-bordeaux',
    };
    ?>
    <div class="flex items-start gap-4 rounded-xl border border-creme bg-white p-5 shadow-sm transition-shadow hover:shadow-md">
        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg <?= $classesBadge ?>">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                <?= icone_stat($icone) ?>
            </svg>
        </div>
        <div>
            <p class="text-xs uppercase tracking-wide text-gris-chaud"><?= View::e($label) ?></p>
            <p class="mt-1 font-voice text-2xl font-medium text-charbon"><?= View::e($valeur) ?></p>
        </div>
    </div>
    <?php
};
?>

<div class="space-y-6">

    <!-- Cartes principales : CA jour, semaine, mois, + commandes enregistrées en 4e carte -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <?= $carteStat('argent', "CA — aujourd'hui", $formaterMontant($statistiques->chiffreAffairesJour), 'violet') ?>
        <?= $carteStat('calendrier', 'CA — 7 derniers jours', $formaterMontant($statistiques->chiffreAffairesSemaine), 'info') ?>
        <?= $carteStat('graphique', 'CA — ce mois', $formaterMontant($statistiques->chiffreAffairesMois), 'succes') ?>
        <?= $carteStat('commandes', 'Commandes enregistrées', (string) $statistiques->nombreCommandes, 'alerte') ?>
    </div>

    <!-- Commandes en cours, isolée : c'est un indicateur d'action à traiter, pas un simple chiffre de suivi -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <?= $carteStat('sablier', 'Commandes en cours', (string) $statistiques->commandesEnCours, 'alerte') ?>
        <div class="flex items-center justify-center rounded-xl border border-dashed border-creme bg-white p-5 text-sm text-gris-chaud">
            Graphique d'évolution des commandes — à venir
        </div>
    </div>

    <!-- Produits -->
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">

        <div class="rounded-xl border border-creme bg-white p-5 shadow-sm lg:col-span-1">
            <div class="mb-3 flex items-center gap-2">
                <svg class="h-4 w-4 text-or" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                    <?= icone_stat('trophee') ?>
                </svg>
                <p class="text-xs uppercase tracking-wide text-gris-chaud">Produit le plus vendu</p>
            </div>
            <p class="text-sm font-medium text-charbon">
                <?= View::e($statistiques->produitLePlusVendu) ?>
            </p>
        </div>

        <div class="rounded-xl border border-creme bg-white p-5 shadow-sm lg:col-span-2">
            <div class="mb-3 flex items-center gap-2">
                <svg class="h-4 w-4 text-or" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                    <?= icone_stat('classement') ?>
                </svg>
                <p class="text-xs uppercase tracking-wide text-gris-chaud">Top 3 produits</p>
            </div>

            <?php if (empty($statistiques->top3Produits)): ?>
                <p class="text-sm text-gris-chaud">Aucune vente enregistrée pour le moment.</p>
            <?php else: ?>
                <ol class="list-none space-y-2.5">
                    <?php foreach ($statistiques->top3Produits as $index => $libelle): ?>
                        <li class="flex items-center gap-3 text-sm text-charbon">
                            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-bordeaux text-xs font-medium text-ivoire">
                                <?= $index + 1 ?>
                            </span>
                            <?= View::e($libelle) ?>
                        </li>
                    <?php endforeach; ?>
                </ol>
            <?php endif; ?>
        </div>

        
    </div>

</div>