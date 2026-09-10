<?php

use Core\View;

$titrePage = 'Commandes impayées';
$section = 'paiements';
?>

<div class="space-y-6">

    <?php if (empty($commandes)): ?>
        <div class="rounded-xl border border-dashed border-creme bg-white p-10 text-center text-sm text-gris-chaud">
            Aucune commande impayée ou partiellement payée pour le moment.
        </div>
    <?php else: ?>
        <div class="overflow-hidden rounded-xl border border-creme bg-white shadow-sm">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-creme text-xs uppercase tracking-wide text-gris-chaud">
                        <th class="px-5 py-3 font-medium">Commande</th>
                        <th class="px-5 py-3 font-medium">Montant total</th>
                        <th class="px-5 py-3 font-medium">Statut paiement</th>
                        <th class="px-5 py-3 font-medium text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-creme">
                    <?php foreach ($commandes as $commande): ?>
                        <tr class="hover:bg-ivoire">
                            <td class="px-5 py-3 font-medium text-charbon"><?= View::e($commande->getNumeroCommande()) ?></td>
                            <td class="px-5 py-3 text-charbon"><?= number_format($commande->getMontantTotal(), 0, ',', ' ') ?> FCFA</td>
                            <td class="px-5 py-3">
                                <?php if ($commande->getStatutPaiement()->value === 'IMPAYE'): ?>
                                    <span class="rounded-full border border-danger/30 bg-danger/10 px-2.5 py-0.5 text-xs font-medium text-danger">Impayée</span>
                                <?php else: ?>
                                    <span class="rounded-full border border-or/30 bg-or/10 px-2.5 py-0.5 text-xs font-medium text-or-clair">Partielle</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <a href="/gerant/paiements/<?= $commande->getId() ?>" class="rounded-md border border-creme px-3 py-1.5 text-xs font-medium text-charbon transition-colors hover:bg-ivoire">
                                    Gérer les paiements
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

</div>