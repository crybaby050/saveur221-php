<?php

use Core\View;

$titrePage = 'Mes commandes';

$libellesStatuts = [
    'EN_ATTENTE' => 'En attente',
    'EN_PREPARATION' => 'En préparation',
    'PRETE' => 'Prête',
    'RETIREE' => 'Retirée',
    'ANNULEE' => 'Annulée',
];

function classe_statut_commande(string $statut): string
{
    return match ($statut) {
        'EN_ATTENTE' => 'bg-amber-100 text-amber-700',
        'EN_PREPARATION' => 'bg-rouge/10 text-rouge',
        'PRETE' => 'bg-green-100 text-green-700',
        'RETIREE' => 'bg-gray-100 text-gris-chaud',
        'ANNULEE' => 'bg-red-100 text-red-600',
        default => 'bg-creme text-charbon',
    };
}
?>

<div class="mx-auto max-w-2xl">

    <h1 class="font-voice text-2xl font-black text-charbon">Mes commandes</h1>

    <?php if (empty($commandes)): ?>
        <div class="mt-8 rounded-3xl bg-white p-10 text-center shadow-sm">
            <p class="text-sm text-gris-chaud">Vous n'avez pas encore passé de commande.</p>
            <a href="/produits" class="mt-4 inline-block rounded-full bg-rouge px-5 py-2.5 text-sm font-bold text-white transition-colors hover:bg-rouge-clair">
                Voir le menu
            </a>
        </div>
    <?php else: ?>

        <div class="mt-5 space-y-3">
            <?php foreach ($commandes as $commande): ?>
                <a href="/commandes/<?= $commande->getId() ?>" class="flex items-center justify-between gap-4 rounded-2xl bg-white p-4 shadow-sm transition-shadow hover:shadow-md">
                    <div class="min-w-0">
                        <p class="text-sm font-bold text-charbon"><?= View::e($commande->getNumeroCommande()) ?></p>
                        <p class="mt-0.5 text-xs text-gris-chaud"><?= $commande->getDateCommande()->format('d/m/Y à H:i') ?></p>
                    </div>

                    <div class="flex shrink-0 items-center gap-3">
                        <span class="rounded-full px-2.5 py-1 text-[10px] font-bold <?= classe_statut_commande($commande->getStatut()->value) ?>">
                            <?= View::e($libellesStatuts[$commande->getStatut()->value] ?? $commande->getStatut()->value) ?>
                        </span>
                        <span class="text-sm font-black text-rouge"><?= number_format($commande->getMontantTotal(), 0, ',', ' ') ?> FCFA</span>
                        <svg class="h-4 w-4 text-gris-chaud" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                            <path d="M9 18l6-6-6-6" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>

    <?php endif; ?>

</div>