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
        'classement' => '<path d="M4 21V10M12 21V4M20 21v-7" stroke-linecap="round"/>',
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

/*
 * Libellé et couleur d'un statut de commande, sans dépendre de l'enum
 * StatutCommande directement dans la vue — comparaison sur ->value pour
 * ne pas avoir à l'importer ici.
 */
$badgeStatut = static function (string $valeurStatut): array {
    return match ($valeurStatut) {
        'EN_ATTENTE' => ['label' => 'En attente', 'classes' => 'bg-alerte/10 text-alerte'],
        'EN_PREPARATION' => ['label' => 'En préparation', 'classes' => 'bg-info/10 text-info'],
        'PRETE' => ['label' => 'Prête', 'classes' => 'bg-succes/10 text-succes'],
        default => ['label' => $valeurStatut, 'classes' => 'bg-creme text-gris-chaud'],
    };
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

    <!-- Graphique d'évolution + Top 3 produits -->
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">

        <div class="rounded-xl border border-creme bg-white p-5 shadow-sm lg:col-span-2">
            <p class="mb-4 text-sm font-medium text-charbon">Évolution des commandes</p>
            <canvas id="graphique-commandes" height="220"></canvas>
            <p class="mt-3 text-xs text-gris-chaud">Données d'exemple — à connecter aux commandes réelles.</p>
        </div>

        <div class="rounded-xl border border-creme bg-white p-5 shadow-sm lg:col-span-1">
            <div class="mb-3 flex items-center gap-2">
                <svg class="h-4 w-4 text-or" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                    <?= icone_stat('classement') ?>
                </svg>
                <p class="text-xs uppercase tracking-wide text-gris-chaud">Top 3 produits les plus vendus</p>
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

    <!-- Commandes en cours -->
    <div class="rounded-xl border border-creme bg-white shadow-sm">
        <div class="border-b border-creme px-5 py-4">
            <p class="text-sm font-medium text-charbon">Commandes en cours</p>
        </div>

        <?php if (empty($commandesEnCours)): ?>
            <p class="px-5 py-6 text-sm text-gris-chaud">Aucune commande en cours actuellement.</p>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-creme text-xs uppercase tracking-wide text-gris-chaud">
                            <th class="px-5 py-3 font-medium">Numéro</th>
                            <th class="px-5 py-3 font-medium">Date</th>
                            <th class="px-5 py-3 font-medium">Montant</th>
                            <th class="px-5 py-3 font-medium">Statut</th>
                            <th class="px-5 py-3 font-medium text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-creme">
                        <?php foreach ($commandesEnCours as $commande): ?>
                            <?php $badge = $badgeStatut($commande->getStatut()->value); ?>
                            <tr class="hover:bg-ivoire">
                                <td class="px-5 py-3 font-medium text-charbon"><?= View::e($commande->getNumeroCommande()) ?></td>
                                <td class="px-5 py-3 text-gris-chaud"><?= View::e($formaterDate($commande->getDateCommande())) ?></td>
                                <td class="px-5 py-3 text-charbon"><?= View::e($formaterMontant($commande->getMontantTotal())) ?></td>
                                <td class="px-5 py-3">
                                    <span class="rounded-full px-2.5 py-1 text-xs font-medium <?= $badge['classes'] ?>">
                                        <?= View::e($badge['label']) ?>
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-right">
                                    
                                        href="/gerant/commandes/<?= $commande->getId() ?>"
                                        class="inline-flex items-center gap-1.5 rounded-md border border-creme px-2.5 py-1.5 text-xs font-medium text-charbon transition-colors hover:bg-ivoire"
                                    >
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                                            <?= icone_stat('oeil') ?>
                                        </svg>
                                        Traiter
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
    // Données statiques temporaires, en attendant de les connecter aux
    // vraies commandes (regroupées par jour) une fois ce chantier prévu.
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
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { color: '#EFEFF7' } },
                x: { grid: { display: false } },
            },
        },
    });
</script>