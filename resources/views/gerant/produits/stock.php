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

    <?php include __DIR__ . '/../../partials/contenu-stock.php'; ?>

</div>