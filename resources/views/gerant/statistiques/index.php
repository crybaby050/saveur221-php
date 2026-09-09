<?php

use Core\View;

$titrePage = 'Tableau de bord';
$section = 'dashboard';

$formaterMontant = static fn(float $montant): string => number_format($montant, 0, ',', ' ') . ' FCFA';
$formaterDate = static fn(\DateTimeImmutable $date): string => $date->format('d/m/Y H:i');

function icone_stat(string $nom): string
{
    $icones = [
        'argent' => '<circle cx="12" cy="12" r="9"/><path d="M9.5 9.5c0-1.1 1.1-2 2.5-2s2.5.9 2.5 2c0 2-5 1.5-5 4 0 1.1 1.1 2 2.5 2s2.5-.9 2.5-2M12 6.5v1M12 16.5v1"/>',
        'calendrier' => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4" stroke-linecap="round"/>',
        'graphique' => '<path d="M4 20V10M10 20V4M16 20v-7M22 20H2" stroke-linecap="round" stroke-linejoin="round"/>',
        'commandes' => '<path d="M6 3h12l1 5H5l1-5Z"/><path d="M5 8v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V8"/><path d="M9 12h6" stroke-linecap="round"/>',
        'trophee' => '<path d="M8 4h8v5a4 4 0 0 1-8 0V4Z"/><path d="M8 5H5a3 3 0 0 0 3 3M16 5h3a3 3 0 0 1-3 3"/><path d="M12 13v3M9 20h6M10 16.5h4v3.5h-4z"/>',
        'oeil' => '<path d="M1.5 12S5 5 12 5s10.5 7 10.5 7-3.5 7-10.5 7S1.5 12 1.5 12Z"/><circle cx="12" cy="12" r="3"/>',
    ];

    return $icones[$nom] ?? '';
}

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

$badgeStatut = static function (string $valeurStatut): array {
    return match ($valeurStatut) {
        'EN_ATTENTE' => ['label' => 'En attente', 'classes' => 'bg-alerte/10 text-alerte'],
        'EN_PREPARATION' => ['label' => 'En préparation', 'classes' => 'bg-info/10 text-info'],
        'PRETE' => ['label' => 'Prête', 'classes' => 'bg-succes/10 text-succes'],
        default => ['label' => $valeurStatut, 'classes' => 'bg-creme text-gris-chaud'],
    };
};

/*
 * Sépare "Nom du produit (N vendus)" en libellé + quantité, pour un
 * affichage en deux lignes sur les cartes du top 3. Repli sur l'affichage
 * brut si le format ne correspond pas (ex: "Aucune vente enregistrée").
 */
$parserTopProduit = static function (string $libelle): array {
    if (preg_match('/^(.+) \((\d+) vendus\)$/', $libelle, $correspondances) === 1) {
        return ['nom' => $correspondances[1], 'quantite' => $correspondances[2] . ' vendus'];
    }

    return ['nom' => $libelle, 'quantite' => null];
};
?>

<div class="space-y-6">

    <!-- Cartes principales -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <?= $carteStat('argent', "CA — aujourd'hui", $formaterMontant($statistiques->chiffreAffairesJour), 'violet') ?>
        <?= $carteStat('calendrier', 'CA — 7 derniers jours', $formaterMontant($statistiques->chiffreAffairesSemaine), 'info') ?>
        <?= $carteStat('graphique', 'CA — ce mois', $formaterMontant($statistiques->chiffreAffairesMois), 'succes') ?>
        <?= $carteStat('commandes', 'Commandes enregistrées', (string) $statistiques->nombreCommandes, 'alerte') ?>
    </div>

    <!-- Graphique + Commandes en cours, même hauteur -->
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">

        <div class="flex h-[420px] flex-col rounded-xl border border-creme bg-white p-5 shadow-sm lg:col-span-2">
            <p class="mb-4 text-sm font-medium text-charbon">Évolution des commandes</p>
            <div class="flex-1">
                <canvas id="graphique-commandes"></canvas>
            </div>
            <p class="mt-3 text-xs text-gris-chaud">Données d'exemple — à connecter aux commandes réelles.</p>
        </div>

        <div class="flex h-[420px] flex-col rounded-xl border border-creme bg-white shadow-sm lg:col-span-1">
            <div class="border-b border-creme px-5 py-4">
                <p class="text-sm font-medium text-charbon">Commandes en cours</p>
            </div>

            <?php if (empty($commandesEnCours)): ?>
                <p class="px-5 py-6 text-sm text-gris-chaud">Aucune commande en cours actuellement.</p>
            <?php else: ?>
                <div class="flex-1 overflow-y-auto">
                    <?php foreach ($commandesEnCours as $commande): ?>
                        <?php $badge = $badgeStatut($commande->getStatut()->value); ?>
                        <div class="flex items-center justify-between gap-3 border-b border-creme px-5 py-3 last:border-b-0">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium text-charbon"><?= View::e($commande->getNumeroCommande()) ?></p>
                                <p class="mt-0.5 text-xs text-gris-chaud"><?= View::e($formaterMontant($commande->getMontantTotal())) ?></p>
                                <span class="mt-1 inline-block rounded-full px-2 py-0.5 text-[11px] font-medium <?= $badge['classes'] ?>">
                                    <?= View::e($badge['label']) ?>
                                </span>
                            </div>
                            
                            <a href="/gerant/commandes/<?= $commande->getId() ?>"
                                aria-label="Traiter la commande <?= View::e($commande->getNumeroCommande()) ?>"
                                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md border border-creme text-charbon transition-colors hover:bg-ivoire"
                            >
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                                    <?= icone_stat('oeil') ?>
                                </svg>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    </div>

    <!-- Top 3 produits, une carte par produit -->
    <?php if (!empty($statistiques->top3Produits)): ?>
        <div>
            <p class="mb-3 text-sm font-medium text-charbon">Top 3 produits les plus vendus</p>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <?php foreach ($statistiques->top3Produits as $index => $libelle): ?>
                    <?php $produit = $parserTopProduit($libelle); ?>
                    <div class="relative rounded-xl border border-creme bg-white p-5 shadow-sm">
                        <span class="absolute right-3 top-3 flex h-7 w-7 items-center justify-center rounded-full bg-bordeaux text-xs font-medium text-ivoire">
                            <?= $index + 1 ?>
                        </span>
                        <div class="mb-3 flex h-9 w-9 items-center justify-center rounded-lg bg-or/10 text-or">
                            <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                                <?= icone_stat('trophee') ?>
                            </svg>
                        </div>
                        <p class="pr-8 text-sm font-medium text-charbon"><?= View::e($produit['nom']) ?></p>
                        <?php if ($produit['quantite'] !== null): ?>
                            <p class="mt-0.5 text-xs text-gris-chaud"><?= View::e($produit['quantite']) ?></p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
    new Chart(document.getElementById('graphique-commandes'), {
        type: 'line',
        data: {
            labels: ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'],
            datasets: [{
                label: 'Commandes',
                data: [8, 12, 7, 15, 20, 26, 14],
                borderColor: '#6C5CE7',
                backgroundColor: 'rgba(108, 92, 231, 0.1)',
                fill: true,
                tension: 0.35,
                pointRadius: 3,
                pointBackgroundColor: '#6C5CE7',
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { color: '#EFEFF7' } },
                x: { grid: { display: false } },
            },
        },
    });
</script>