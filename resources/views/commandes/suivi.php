<?php

use Core\View;

$titrePage = 'Suivi de commande';

$etapes = ['EN_ATTENTE', 'EN_PREPARATION', 'PRETE', 'RETIREE'];
$libellesEtapes = [
    'EN_ATTENTE' => 'Reçue',
    'EN_PREPARATION' => 'En préparation',
    'PRETE' => 'Prête',
    'RETIREE' => 'Retirée',
];

$statutActuel = $commande->getStatut()->value;
$estAnnulee = $statutActuel === 'ANNULEE';
$indexActuel = array_search($statutActuel, $etapes, true);
?>

<div class="mx-auto max-w-2xl">

    <a href="/commandes/historique" class="inline-flex items-center gap-1.5 text-sm text-gris-chaud transition-colors hover:text-rouge">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
            <path d="M15 18l-6-6 6-6" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        Mes commandes
    </a>

    <div class="mt-4 rounded-3xl bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="font-voice text-xl font-black text-charbon"><?= View::e($commande->getNumeroCommande()) ?></p>
                <p class="mt-0.5 text-sm text-gris-chaud"><?= $commande->getDateCommande()->format('d/m/Y à H:i') ?></p>
            </div>
            <p class="text-xl font-black text-rouge"><?= number_format($commande->getMontantTotal(), 0, ',', ' ') ?> FCFA</p>
        </div>

        <?php if ($estAnnulee): ?>
            <div class="mt-6 rounded-2xl border-l-4 border-rouge bg-rouge/10 px-4 py-3 text-sm text-rouge">
                Cette commande a été annulée.
            </div>
        <?php else: ?>
            <div class="mt-8 flex items-start justify-between">
                <?php foreach ($etapes as $position => $etape): ?>
                    <?php
                    $estAtteinte = $position <= $indexActuel;
                    $estCourante = $position === $indexActuel;
                    ?>
                    <div class="flex flex-1 flex-col items-center text-center">
                        <div class="flex items-center gap-1 self-stretch">
                            <?php if ($position > 0): ?>
                                <div class="h-0.5 flex-1 <?= $estAtteinte ? 'bg-rouge' : 'bg-creme' ?>"></div>
                            <?php else: ?>
                                <div class="h-0.5 flex-1 bg-transparent"></div>
                            <?php endif; ?>

                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full <?= $estAtteinte ? 'bg-rouge text-white' : 'border-2 border-creme bg-white text-gris-chaud' ?> <?= $estCourante ? 'ring-4 ring-rouge/15' : '' ?>">
                                <?php if ($estAtteinte && !$estCourante): ?>
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                <?php else: ?>
                                    <span class="text-xs font-bold"><?= $position + 1 ?></span>
                                <?php endif; ?>
                            </div>

                            <?php if ($position < count($etapes) - 1): ?>
                                <div class="h-0.5 flex-1 <?= $position < $indexActuel ? 'bg-rouge' : 'bg-creme' ?>"></div>
                            <?php else: ?>
                                <div class="h-0.5 flex-1 bg-transparent"></div>
                            <?php endif; ?>
                        </div>

                        <span class="mt-2 text-[10px] font-bold <?= $estAtteinte ? 'text-charbon' : 'text-gris-chaud' ?>">
                            <?= View::e($libellesEtapes[$etape]) ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="mt-4 flex justify-center">
        <a href="/commandes/<?= $commande->getId() ?>" class="rounded-full border border-creme bg-white px-5 py-2.5 text-sm font-medium text-charbon transition-colors hover:bg-creme">
            Voir le détail de la commande
        </a>
    </div>

</div>