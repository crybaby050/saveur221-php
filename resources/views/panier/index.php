<?php

use Core\View;

$titrePage = 'Mon panier';
?>

<div class="mx-auto max-w-3xl">

    <h1 class="font-voice text-2xl font-black text-charbon">Mon panier</h1>

    <?php if (isset($erreur)): ?>
        <p class="mt-4 rounded-2xl border-l-4 border-rouge bg-rouge/10 px-4 py-3 text-sm text-rouge">
            <?= View::e($erreur) ?>
        </p>
    <?php endif; ?>

    <?php if (empty($lignes)): ?>
        <div class="mt-8 rounded-3xl bg-white p-10 text-center shadow-sm">
            <p class="text-sm text-gris-chaud">Votre panier est vide.</p>
            <a href="/produits" class="mt-4 inline-block rounded-full bg-rouge px-5 py-2.5 text-sm font-bold text-white transition-colors hover:bg-rouge-clair">
                Voir le menu
            </a>
        </div>
    <?php else: ?>

        <div class="mt-5 divide-y divide-creme rounded-3xl bg-white shadow-sm">
            <?php foreach ($lignes as $produitId => $ligne): ?>
                <?php $produit = $ligne['produit']; ?>
                <div class="flex items-center gap-4 p-4">
                    <img
                        src="<?= View::e($produit->getImage() ?? '/assets/images/produit-defaut.svg') ?>"
                        alt=""
                        class="h-16 w-16 shrink-0 rounded-2xl object-cover"
                    >

                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-bold text-charbon"><?= View::e($produit->getLibelle()) ?></p>
                        <p class="mt-0.5 text-xs text-gris-chaud"><?= number_format($produit->getPrix(), 0, ',', ' ') ?> FCFA / unité</p>

                        <form method="post" action="/panier/modifier" class="mt-2 flex items-center gap-2">
                            <input type="hidden" name="produit_id" value="<?= $produitId ?>">
                            <div class="inline-flex items-center gap-3 rounded-full border border-creme px-2 py-1">
                                <button type="button"
                                    onclick="ajusterQuantitePanier(<?= $produitId ?>, -1, <?= $produit->getQuantiteStock() ?>)"
                                    class="flex h-6 w-6 items-center justify-center rounded-full text-sm font-bold text-charbon transition-colors hover:bg-creme">
                                    −
                                </button>
                                <span id="quantite-affichee-<?= $produitId ?>" class="w-4 text-center text-xs font-bold text-charbon"><?= $ligne['quantite'] ?></span>
                                <button type="button"
                                    onclick="ajusterQuantitePanier(<?= $produitId ?>, 1, <?= $produit->getQuantiteStock() ?>)"
                                    class="flex h-6 w-6 items-center justify-center rounded-full text-sm font-bold text-charbon transition-colors hover:bg-creme">
                                    +
                                </button>
                            </div>
                            <input type="hidden" id="quantite-input-<?= $produitId ?>" name="quantite" value="<?= $ligne['quantite'] ?>">
                            <button type="submit" class="text-xs font-medium text-rouge hover:underline">
                                Mettre à jour
                            </button>
                        </form>
                    </div>

                    <div class="flex shrink-0 flex-col items-end gap-2">
                        <span class="text-sm font-black text-rouge"><?= number_format($ligne['sousTotal'], 0, ',', ' ') ?> FCFA</span>
                        <form method="post" action="/panier/retirer/<?= $produitId ?>">
                            <button type="submit" class="text-xs text-gris-chaud transition-colors hover:text-rouge">
                                Retirer
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="mt-5 rounded-3xl bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-sm font-bold text-charbon">Total</span>
                <span class="text-xl font-black text-rouge"><?= number_format($montantTotal, 0, ',', ' ') ?> FCFA</span>
            </div>

            <form method="post" action="/commande/valider" class="mt-4">
                <button type="submit" class="flex w-full items-center justify-center rounded-full bg-rouge py-3.5 text-sm font-bold text-white transition-colors hover:bg-rouge-clair">
                    Valider la commande
                </button>
            </form>

            <form method="post" action="/panier/vider" class="mt-2">
                <button type="submit" class="flex w-full items-center justify-center rounded-full border border-creme py-3 text-sm font-medium text-gris-chaud transition-colors hover:bg-creme">
                    Vider le panier
                </button>
            </form>
        </div>

    <?php endif; ?>

</div>

<script>
    function ajusterQuantitePanier(produitId, delta, stockMax) {
        const input = document.getElementById(`quantite-input-${produitId}`);
        const affichage = document.getElementById(`quantite-affichee-${produitId}`);
        const nouvelleValeur = Math.min(stockMax, Math.max(1, parseInt(input.value, 10) + delta));
        input.value = nouvelleValeur;
        affichage.textContent = nouvelleValeur;
    }
</script>