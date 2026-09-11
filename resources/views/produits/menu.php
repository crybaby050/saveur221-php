<?php

use Core\View;

$titrePage = 'Menu';
?>

<section class="mt-5">
    <h1 class="font-voice text-2xl font-black text-charbon">Notre menu</h1>

    <!-- Recherche (desktop uniquement, le mobile a déjà une barre dans le layout) -->
    <form method="get" action="/produits" class="mt-4 hidden items-center gap-3 rounded-2xl border border-rouge/10 bg-white px-4 py-3 shadow-sm lg:flex">
        <svg class="h-4.5 w-4.5 text-rouge" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
            <circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3" stroke-linecap="round"/>
        </svg>
        <input
            type="text"
            name="recherche"
            value="<?= View::e($_GET['recherche'] ?? '') ?>"
            placeholder="Rechercher un plat..."
            class="flex-1 bg-transparent text-sm text-charbon placeholder:text-gris-chaud focus:outline-none"
        >
        <button type="submit" class="rounded-full bg-rouge px-4 py-1.5 text-xs font-bold text-white">Chercher</button>
    </form>

    <!-- Catégories -->
    <?php if (!empty($categories)): ?>
        <div class="hide-scrollbar mt-4 flex gap-3 overflow-x-auto pb-2">
            <a href="/produits" class="flex min-w-[65px] flex-col items-center gap-2">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl border <?= $categorieActive === null ? 'border-rouge' : 'border-creme' ?> bg-white shadow-sm">
                    <svg class="h-6 w-6 <?= $categorieActive === null ? 'text-rouge' : 'text-gris-chaud' ?>" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                        <rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>
                    </svg>
                </div>
                <span class="text-[10px] font-semibold <?= $categorieActive === null ? 'text-rouge' : 'text-charbon' ?>">Tout</span>
            </a>

            <?php foreach ($categories as $categorie): ?>
                <a href="/produits?categorie=<?= $categorie->getId() ?>" class="flex min-w-[65px] flex-col items-center gap-2">
                    <div class="flex h-14 w-14 items-center justify-center overflow-hidden rounded-2xl border <?= $categorieActive === $categorie->getId() ? 'border-rouge' : 'border-creme' ?> bg-white shadow-sm">
                        <img src="<?= View::e($categorie->getImage() ?? '/assets/images/categorie-defaut.svg') ?>" alt="" class="h-full w-full object-cover">
                    </div>
                    <span class="text-center text-[10px] font-semibold <?= $categorieActive === $categorie->getId() ? 'text-rouge' : 'text-charbon' ?>">
                        <?= View::e($categorie->getNom()) ?>
                    </span>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Grille produits -->
    <?php if (empty($produits)): ?>
        <p class="mt-6 text-sm text-gris-chaud">Aucun produit trouvé.</p>
    <?php else: ?>
        <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
            <?php foreach ($produits as $produit): ?>
                <article class="overflow-hidden rounded-2xl bg-white shadow-sm">
                    <a href="/produits/<?= $produit->getId() ?>" class="relative block h-36 overflow-hidden bg-creme">
                        <img
                            src="<?= View::e($produit->getImage() ?? '/assets/images/produit-defaut.svg') ?>"
                            alt="<?= View::e($produit->getLibelle()) ?>"
                            class="h-full w-full object-cover transition duration-300 hover:scale-105"
                        >
                    </a>

                    <div class="p-3">
                        <a href="/produits/<?= $produit->getId() ?>">
                            <p class="text-[11px] font-bold text-charbon"><?= View::e($produit->getLibelle()) ?></p>
                        </a>
                        <?php if ($produit->getDescription()): ?>
                            <p class="mt-1 line-clamp-1 text-[9px] text-gris-chaud"><?= View::e($produit->getDescription()) ?></p>
                        <?php endif; ?>

                        <div class="mt-3 flex items-center justify-between">
                            <span class="text-xs font-black text-rouge">
                                <?= number_format($produit->getPrix(), 0, ',', ' ') ?> FCFA
                            </span>

                            <?php if ($produit->isDisponible()): ?>
                                <form method="post" action="/panier/ajouter">
                                    <input type="hidden" name="produit_id" value="<?= $produit->getId() ?>">
                                    <input type="hidden" name="quantite" value="1">
                                    <button type="submit" class="flex h-7 w-7 items-center justify-center rounded-full bg-rouge text-sm font-bold text-white transition-colors hover:bg-rouge-clair">
                                        +
                                    </button>
                                </form>
                            <?php else: ?>
                                <span class="rounded-full bg-creme px-2 py-1 text-[9px] font-medium text-gris-chaud">Indisponible</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>