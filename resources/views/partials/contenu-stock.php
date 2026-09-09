<?php

use Core\View;

/*
 * Contenu du suivi de stock (résumé + listes), réutilisé à la fois par la
 * page complète gerant/produits/stock.php et par le tiroir ouvert depuis
 * gerant/produits/index.php. Attend $produits, $stockFaible, $ruptures
 * (et optionnellement $erreur) dans la portée appelante.
 */
$produits ??= [];
$stockFaible ??= [];
$ruptures ??= [];
?>

<!-- Résumé -->
<div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
    <div class="rounded-xl border border-danger/20 bg-danger/5 p-5">
        <p class="text-xs font-medium uppercase tracking-wide text-danger">Ruptures de stock</p>
        <p class="mt-1 font-voice text-2xl font-medium text-charbon"><?= count($ruptures) ?></p>
    </div>
    <div class="rounded-xl border border-or/20 bg-or/5 p-5">
        <p class="text-xs font-medium uppercase tracking-wide text-or-clair">Stock faible</p>
        <p class="mt-1 font-voice text-2xl font-medium text-charbon"><?= count($stockFaible) ?></p>
    </div>
</div>

<?php if (isset($erreur)): ?>
    <p class="mt-4 rounded-md border-l-4 border-danger bg-danger/10 px-3.5 py-2.5 text-sm text-danger">
        <?= View::e($erreur) ?>
    </p>
<?php endif; ?>

<?php if (!empty($ruptures)): ?>
    <div class="mt-4 rounded-xl border border-creme bg-white shadow-sm">
        <div class="border-b border-creme px-5 py-3">
            <p class="text-sm font-medium text-charbon">Produits en rupture</p>
        </div>
        <div class="divide-y divide-creme">
            <?php foreach ($ruptures as $produit): ?>
                <?php include __DIR__ . '/ligne-stock.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<?php if (!empty($stockFaible)): ?>
    <div class="mt-4 rounded-xl border border-creme bg-white shadow-sm">
        <div class="border-b border-creme px-5 py-3">
            <p class="text-sm font-medium text-charbon">Stock faible</p>
        </div>
        <div class="divide-y divide-creme">
            <?php foreach ($stockFaible as $produit): ?>
                <?php include __DIR__ . '/ligne-stock.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<div class="mt-4 rounded-xl border border-creme bg-white shadow-sm">
    <div class="border-b border-creme px-5 py-3">
        <p class="text-sm font-medium text-charbon">Tous les produits</p>
    </div>
    <div class="divide-y divide-creme">
        <?php foreach ($produits as $produit): ?>
            <?php include __DIR__ . '/ligne-stock.php'; ?>
        <?php endforeach; ?>
    </div>
</div>