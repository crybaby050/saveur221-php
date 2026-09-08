<?php

use Core\View;

/*
 * Tableau de bord statistique (US "Consulter le tableau de bord
 * statistique"). $statistiques est une instance de App\Models\Statistiques,
 * propriétés publiques en lecture seule — accédées directement, sans
 * getters.
 */

$titrePage = 'Tableau de bord';
$section = 'dashboard';

$formaterMontant = static fn(float $montant): string => number_format($montant, 0, ',', ' ') . ' FCFA';
?>

<div class="space-y-6">

    <!-- Cartes chiffre d'affaires -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">

        <div class="rounded-lg border border-or-clair bg-white p-5">
            <p class="text-xs uppercase tracking-wide text-gris-chaud">Chiffre d'affaires — jour</p>
            <p class="mt-2 font-voice text-2xl font-medium text-charbon">
                <?= View::e($formaterMontant($statistiques->chiffreAffairesJour)) ?>
            </p>
        </div>

        <div class="rounded-lg border border-or-clair bg-white p-5">
            <p class="text-xs uppercase tracking-wide text-gris-chaud">Chiffre d'affaires — 7 derniers jours</p>
            <p class="mt-2 font-voice text-2xl font-medium text-charbon">
                <?= View::e($formaterMontant($statistiques->chiffreAffairesSemaine)) ?>
            </p>
        </div>

        <div class="rounded-lg border border-or-clair bg-white p-5">
            <p class="text-xs uppercase tracking-wide text-gris-chaud">Chiffre d'affaires — mois</p>
            <p class="mt-2 font-voice text-2xl font-medium text-charbon">
                <?= View::e($formaterMontant($statistiques->chiffreAffairesMois)) ?>
            </p>
        </div>

    </div>

    <!-- Cartes activité -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

        <div class="rounded-lg border border-or-clair bg-white p-5">
            <p class="text-xs uppercase tracking-wide text-gris-chaud">Commandes enregistrées</p>
            <p class="mt-2 font-voice text-2xl font-medium text-charbon">
                <?= View::e((string) $statistiques->nombreCommandes) ?>
            </p>
        </div>

        <div class="rounded-lg border border-or-clair bg-white p-5">
            <p class="text-xs uppercase tracking-wide text-gris-chaud">Commandes en cours</p>
            <p class="mt-2 font-voice text-2xl font-medium text-charbon">
                <?= View::e((string) $statistiques->commandesEnCours) ?>
            </p>
        </div>

    </div>

    <!-- Produits -->
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">

        <div class="rounded-lg border border-or-clair bg-white p-5 lg:col-span-1">
            <p class="text-xs uppercase tracking-wide text-gris-chaud">Produit le plus vendu</p>
            <p class="mt-2 text-sm font-medium text-charbon">
                <?= View::e($statistiques->produitLePlusVendu) ?>
            </p>
        </div>

        <div class="rounded-lg border border-or-clair bg-white p-5 lg:col-span-2">
            <p class="mb-3 text-xs uppercase tracking-wide text-gris-chaud">Top 3 produits</p>

            <?php if (empty($statistiques->top3Produits)): ?>
                <p class="text-sm text-gris-chaud">Aucune vente enregistrée pour le moment.</p>
            <?php else: ?>
                <ol class="space-y-2">
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