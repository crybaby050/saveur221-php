<?php

use Core\View;

$titrePage = 'Factures';
$section = 'factures';

$facturesAffichees = $factureTrouvee !== null ? [$factureTrouvee] : $factures;
?>

<div class="space-y-6">

    <form method="get" action="/gerant/factures" class="relative w-full sm:max-w-xs">
        <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gris-chaud" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
            <circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3" stroke-linecap="round"/>
        </svg>
        <input
            type="text"
            name="recherche"
            value="<?= View::e($motCle ?? '') ?>"
            placeholder="Rechercher par numéro (FAC-2026-000104)..."
            class="h-10 w-full rounded-md border border-creme bg-white pl-9 pr-3 text-sm text-charbon focus:border-bordeaux focus:outline-none focus:ring-3 focus:ring-bordeaux/10"
        >
    </form>

    <?php if ($aucunResultat): ?>
        <p class="rounded-md border-l-4 border-danger bg-danger/10 px-3.5 py-2.5 text-sm text-danger">
            Aucune facture trouvée avec ce numéro.
        </p>
    <?php endif; ?>

    <?php if (empty($facturesAffichees) && !$aucunResultat): ?>
        <div class="rounded-xl border border-dashed border-creme bg-white p-10 text-center text-sm text-gris-chaud">
            Aucune facture n'a encore été émise.
        </div>
    <?php elseif (!empty($facturesAffichees)): ?>
        <div class="overflow-hidden rounded-xl border border-creme bg-white shadow-sm">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-creme text-xs uppercase tracking-wide text-gris-chaud">
                        <th class="px-5 py-3 font-medium">Numéro</th>
                        <th class="px-5 py-3 font-medium">Commande</th>
                        <th class="px-5 py-3 font-medium">Montant</th>
                        <th class="px-5 py-3 font-medium">Date d'émission</th>
                        <th class="px-5 py-3 font-medium text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-creme">
                    <?php foreach ($facturesAffichees as $facture): ?>
                        <tr class="hover:bg-ivoire">
                            <td class="px-5 py-3 font-medium text-charbon"><?= View::e($facture->getNumeroFacture()) ?></td>
                            <td class="px-5 py-3">
                                <a href="/gerant/commandes/<?= $facture->getCommandeId() ?>" class="text-bordeaux hover:underline">
                                    Voir la commande
                                </a>
                            </td>
                            <td class="px-5 py-3 text-charbon"><?= number_format($facture->getMontantTotal(), 0, ',', ' ') ?> FCFA</td>
                            <td class="px-5 py-3 text-gris-chaud"><?= $facture->getDateEmission()->format('d/m/Y à H:i') ?></td>
                            <td class="px-5 py-3 text-right">
                                <button type="button" onclick="window.print()" class="rounded-md border border-creme px-3 py-1.5 text-xs font-medium text-charbon transition-colors hover:bg-ivoire">
                                    Imprimer
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

</div>