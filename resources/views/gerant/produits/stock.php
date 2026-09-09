<?php

use Core\View;

$titrePage = 'Suivi de stock';
$section = 'produits';
?>

<div class="space-y-6">

    <div class="flex items-center justify-between">
        <a href="/gerant/produits" class="inline-flex items-center gap-1.5 text-sm text-gris-chaud transition-colors hover:text-charbon">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                <path d="M15 18l-6-6 6-6" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Retour aux produits
        </a>
    </div>

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
        <p class="rounded-md border-l-4 border-danger bg-danger/10 px-3.5 py-2.5 text-sm text-danger">
            <?= View::e($erreur) ?>
        </p>
    <?php endif; ?>

    <?php if (!empty($ruptures)): ?>
        <div class="rounded-xl border border-creme bg-white shadow-sm">
            <div class="border-b border-creme px-5 py-3">
                <p class="text-sm font-medium text-charbon">Produits en rupture</p>
            </div>
            <div class="divide-y divide-creme">
                <?php foreach ($ruptures as $produit): ?>
                    <?php include __DIR__ . '/../../partials/ligne-stock.php'; ?>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($stockFaible)): ?>
        <div class="rounded-xl border border-creme bg-white shadow-sm">
            <div class="border-b border-creme px-5 py-3">
                <p class="text-sm font-medium text-charbon">Stock faible</p>
            </div>
            <div class="divide-y divide-creme">
                <?php foreach ($stockFaible as $produit): ?>
                    <?php include __DIR__ . '/../../partials/ligne-stock.php'; ?>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="rounded-xl border border-creme bg-white shadow-sm">
        <div class="border-b border-creme px-5 py-3">
            <p class="text-sm font-medium text-charbon">Tous les produits</p>
        </div>
        <div class="divide-y divide-creme">
            <?php foreach ($produits as $produit): ?>
                <?php include __DIR__ . '/../../partials/ligne-stock.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>

</div>