<?php

use Core\View;

$titrePage = 'Commande';
$section = 'commandes';

$statutsLibelles = [
    'EN_ATTENTE' => 'En attente',
    'EN_PREPARATION' => 'En préparation',
    'PRETE' => 'Prête',
    'RETIREE' => 'Retirée',
    'ANNULEE' => 'Annulée',
];

$prochainStatut = [
    'EN_ATTENTE' => 'EN_PREPARATION',
    'EN_PREPARATION' => 'PRETE',
    'PRETE' => 'RETIREE',
];
?>

<div class="space-y-6">

    <a href="/gerant/commandes" class="inline-flex items-center gap-1.5 text-sm text-gris-chaud transition-colors hover:text-charbon">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
            <path d="M15 18l-6-6 6-6" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        Retour aux commandes
    </a>

    <?php if (isset($erreur)): ?>
        <p class="rounded-md border-l-4 border-danger bg-danger/10 px-3.5 py-2.5 text-sm text-danger">
            <?= View::e($erreur) ?>
        </p>
    <?php endif; ?>

    <?php if ($commande === null): ?>
        <div class="rounded-xl border border-dashed border-creme bg-white p-10 text-center text-sm text-gris-chaud">
            Commande introuvable.
        </div>
    <?php else: ?>

        <div class="rounded-xl border border-creme bg-white p-5 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="font-voice text-lg font-medium text-charbon"><?= View::e($commande->getNumeroCommande()) ?></p>
                    <p class="text-sm text-gris-chaud"><?= $commande->getDateCommande()->format('d/m/Y à H:i') ?></p>
                </div>
                <div class="text-right">
                    <p class="text-xs uppercase tracking-wide text-gris-chaud">Montant total</p>
                    <p class="font-voice text-xl font-medium text-bordeaux"><?= number_format($commande->getMontantTotal(), 0, ',', ' ') ?> FCFA</p>
                </div>
            </div>

            <?php if ($client !== null): ?>
                <div class="mt-4 border-t border-creme pt-4">
                    <p class="text-xs uppercase tracking-wide text-gris-chaud">Client</p>
                    <p class="mt-1 text-sm font-medium text-charbon"><?= View::e(trim($client->getPrenom() . ' ' . $client->getNom())) ?></p>
                    <p class="text-sm text-gris-chaud"><?= View::e($client->getTelephone() ?? '—') ?></p>
                </div>
            <?php endif; ?>

            <div class="mt-4 flex flex-wrap items-center gap-2 border-t border-creme pt-4">
                <?php if ($commande->getStatut()->value !== 'ANNULEE' && $commande->getStatut()->value !== 'RETIREE' && isset($prochainStatut[$commande->getStatut()->value])): ?>
                    <form method="post" action="/gerant/commandes/<?= $commande->getId() ?>/statut">
                        <input type="hidden" name="statut" value="<?= $prochainStatut[$commande->getStatut()->value] ?>">
                        <button type="submit" class="rounded-md bg-bordeaux px-4 py-2 text-sm font-medium text-ivoire transition-colors hover:bg-bordeaux-sombre">
                            Passer à « <?= $statutsLibelles[$prochainStatut[$commande->getStatut()->value]] ?> »
                        </button>
                    </form>
                <?php endif; ?>

                <?php if (!in_array($commande->getStatut()->value, ['ANNULEE', 'RETIREE'], true)): ?>
                    <form method="post" action="/gerant/commandes/<?= $commande->getId() ?>/annuler"
                        onsubmit="return confirm('Annuler cette commande ? Le stock sera restitué.');">
                        <button type="submit" class="rounded-md border border-danger/30 px-4 py-2 text-sm font-medium text-danger transition-colors hover:bg-danger/10">
                            Annuler la commande
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <div class="rounded-xl border border-creme bg-white shadow-sm">
            <div class="border-b border-creme px-5 py-3">
                <p class="text-sm font-medium text-charbon">Articles</p>
            </div>
            <div class="divide-y divide-creme">
                <?php foreach ($lignesEnrichies as $entree): ?>
                    <?php ['ligne' => $ligne, 'produit' => $produit] = $entree; ?>
                    <div class="flex items-center justify-between gap-3 px-5 py-3">
                        <div class="flex items-center gap-3">
                            <img src="<?= View::e($produit?->getImage() ?? '/assets/images/produit-defaut.svg') ?>" alt="" class="h-10 w-10 shrink-0 rounded-md object-cover">
                            <div>
                                <p class="text-sm font-medium text-charbon"><?= View::e($produit?->getLibelle() ?? 'Produit supprimé') ?></p>
                                <p class="text-xs text-gris-chaud"><?= $ligne->getQuantite() ?> × <?= number_format($ligne->getPrixUnitaire(), 0, ',', ' ') ?> FCFA</p>
                            </div>
                        </div>
                        <p class="text-sm font-medium text-charbon"><?= number_format($ligne->calculerSousTotal(), 0, ',', ' ') ?> FCFA</p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

    <?php endif; ?>

</div>