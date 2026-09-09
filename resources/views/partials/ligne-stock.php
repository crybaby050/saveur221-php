<?php
/*
 * Attend $produit (App\Models\Produit) dans la portée appelante.
 * Utilisé par gerant/produits/stock.php pour éviter de répéter la même
 * ligne dans les 3 sections (ruptures, stock faible, tous les produits).
 */
?>
<div class="flex flex-col gap-3 px-5 py-3.5 sm:flex-row sm:items-center sm:justify-between">
    <div class="flex items-center gap-3">
        <img src="<?= View::e($produit->getImage() ?? '/assets/images/produit-defaut.svg') ?>" alt="" class="h-10 w-10 shrink-0 rounded-md object-cover">
        <div>
            <p class="text-sm font-medium text-charbon"><?= View::e($produit->getLibelle()) ?></p>
            <p class="text-xs text-gris-chaud">
                <?= $produit->getQuantiteStock() ?> en stock · seuil d'alerte : <?= $produit->getSeuilAlerte() ?>
            </p>
        </div>
    </div>

    <div class="flex flex-wrap items-center gap-2">
                <form method="post" action="/gerant/produits/<?= $produit->getId() ?>/seuil-alerte" class="flex items-center gap-1.5">
            <input
                type="number"
                name="quantite"
                min="1"
                value="10"
                required
                class="h-9 w-20 rounded-md border border-creme bg-white px-2.5 text-sm text-charbon focus:border-bordeaux focus:outline-none focus:ring-3 focus:ring-bordeaux/10"
            >
            <button type="submit" class="h-9 rounded-md bg-bordeaux px-3 text-xs font-medium text-ivoire transition-colors hover:bg-bordeaux-sombre">
                Approvisionner
            </button>
        </form>

        <form method="post" action="/gerant/produits/<?= $produit->getId() ?>/seuil" class="flex items-center gap-1.5">
            <input
                type="number"
                name="seuil"
                min="0"
                value="<?= $produit->getSeuilAlerte() ?>"
                required
                class="h-9 w-20 rounded-md border border-creme bg-white px-2.5 text-sm text-charbon focus:border-bordeaux focus:outline-none focus:ring-3 focus:ring-bordeaux/10"
            >
            <button type="submit" class="h-9 rounded-md border border-creme px-3 text-xs font-medium text-charbon transition-colors hover:bg-ivoire">
                Seuil
            </button>
        </form>
    </div>
</div>