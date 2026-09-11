<?php

use Core\View;

$titrePage = 'Détail commande';

$libellesStatuts = [
    'EN_ATTENTE' => 'En attente',
    'EN_PREPARATION' => 'En préparation',
    'PRETE' => 'Prête',
    'RETIREE' => 'Retirée',
    'ANNULEE' => 'Annulée',
];

$libellesStatutsPaiement = [
    'IMPAYE' => 'Non payée',
    'PARTIEL' => 'Partiellement payée',
    'PAYEE' => 'Payée',
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

    <a href="/commandes/historique" class="inline-flex items-center gap-1.5 text-sm text-gris-chaud transition-colors hover:text-rouge">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
            <path d="M15 18l-6-6 6-6" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        Mes commandes
    </a>

    <div class="mt-4 rounded-3xl bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="font-voice text-xl font-black text-charbon"><?= View::e($commande->getNumeroCommande()) ?></p>
                <p class="mt-0.5 text-sm text-gris-chaud"><?= $commande->getDateCommande()->format('d/m/Y à H:i') ?></p>
            </div>

            <div class="flex items-center gap-2">
                <span class="rounded-full px-3 py-1.5 text-xs font-bold <?= classe_statut_commande($commande->getStatut()->value) ?>">
                    <?= View::e($libellesStatuts[$commande->getStatut()->value] ?? $commande->getStatut()->value) ?>
                </span>
                <?php if ($commande->getStatut()->value !== 'ANNULEE'): ?>
                    <a href="/commandes/<?= $commande->getId() ?>/suivi" class="text-xs font-medium text-rouge hover:underline">
                        Suivre
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <div class="mt-5 divide-y divide-creme border-t border-creme">
            <?php foreach ($commande->getLignes() as $ligne): ?>
                <div class="flex items-center justify-between py-3 text-sm">
                    <span class="text-charbon">Produit #<?= $ligne->getProduitId() ?> <span class="text-gris-chaud">× <?= $ligne->getQuantite() ?></span></span>
                    <span class="font-medium text-charbon"><?= number_format($ligne->calculerSousTotal(), 0, ',', ' ') ?> FCFA</span>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="mt-4 flex items-center justify-between border-t border-creme pt-4">
            <span class="text-sm font-bold text-charbon">Total</span>
            <span class="text-lg font-black text-rouge"><?= number_format($commande->getMontantTotal(), 0, ',', ' ') ?> FCFA</span>
        </div>
    </div>

    <div class="mt-4 rounded-3xl bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between">
            <p class="text-sm font-bold text-charbon">Paiement</p>
            <?php
            $totalPaye = array_reduce($paiements, fn(float $total, $paiement) => $total + $paiement->getMontant(), 0.0);
            $statutPaiement = match (true) {
                $totalPaye <= 0 => 'IMPAYE',
                $totalPaye < $commande->getMontantTotal() => 'PARTIEL',
                default => 'PAYEE',
            };
            ?>
            <span class="text-xs font-bold text-gris-chaud"><?= $libellesStatutsPaiement[$statutPaiement] ?></span>
        </div>

        <?php if (empty($paiements)): ?>
            <p class="mt-3 text-sm text-gris-chaud">Aucun paiement enregistré pour cette commande.</p>
        <?php else: ?>
            <div class="mt-3 divide-y divide-creme">
                <?php foreach ($paiements as $paiement): ?>
                    <div class="flex items-center justify-between py-2.5 text-sm">
                        <span class="text-gris-chaud"><?= $paiement->getDatePaiement()->format('d/m/Y à H:i') ?></span>
                        <span class="font-medium text-charbon"><?= number_format($paiement->getMontant(), 0, ',', ' ') ?> FCFA</span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

</div>