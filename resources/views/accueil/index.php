<?php

use Core\View;
?>

<!-- Hero -->
<section class="mt-5 overflow-hidden rounded-3xl bg-bleu-vif shadow-sm">
    <div class="flex min-h-[205px] items-center">
        <div class="w-[55%] p-5 sm:p-8">
            <p class="text-[10px] font-bold uppercase tracking-wider text-white/80">Saveur 221</p>
            <h1 class="mt-2 font-voice text-3xl font-black leading-[0.95] text-white sm:text-5xl">
                LE GOÛT.<br>LA PASSION.<br>LE SÉNÉGAL.
            </h1>
            <p class="mt-3 max-w-sm text-[11px] leading-relaxed text-white/80 sm:text-sm">
                Découvrez nos spécialités préparées avec passion.
            </p>
            <a href="/produits" class="mt-4 inline-block rounded-full bg-white px-4 py-2 text-[11px] font-bold text-bleu-vif shadow-sm transition hover:bg-gray-100">
                Voir le menu complet →
            </a>
        </div>
        <div class="relative flex h-full flex-1 items-end justify-center">
            <img src="https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=1200&q=80" alt="" class="h-[205px] w-full object-cover">
        </div>
    </div>
</section>

<!-- Catégories -->
<?php if (!empty($categories)): ?>
    <section class="mt-6">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-black text-charbon">Parcourir par catégorie</h2>
            <a href="/produits" class="text-xs font-bold text-rouge">Tout voir</a>
        </div>
        <div class="hide-scrollbar mt-3 flex gap-3 overflow-x-auto pb-2">
            <?php foreach ($categories as $categorie): ?>
                <a href="/produits?categorie=<?= $categorie->getId() ?>" class="flex min-w-[65px] flex-col items-center gap-2">
                    <div class="flex h-14 w-14 items-center justify-center overflow-hidden rounded-2xl border border-creme bg-white shadow-sm">
                        <img src="<?= View::e($categorie->getImage() ?? '/assets/images/categorie-defaut.svg') ?>" alt="" class="h-full w-full object-cover">
                    </div>
                    <span class="text-center text-[10px] font-semibold text-charbon">
                        <?= View::e($categorie->getNom()) ?>
                    </span>
                </a>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>

<!-- Suggestions -->
<?php if (!empty($suggestions)): ?>
    <section class="mt-6">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-black text-charbon">Nos incontournables</h2>
            <a href="/produits" class="text-xs font-bold text-rouge">Voir tout le menu</a>
        </div>
        <div class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-4">
            <?php foreach ($suggestions as $produit): ?>
                <article class="overflow-hidden rounded-2xl bg-white shadow-sm">
                    <a href="/produits/<?= $produit->getId() ?>" class="relative block h-32 overflow-hidden bg-creme">
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
                        <p class="mt-2 text-xs font-black text-rouge">
                            <?= number_format($produit->getPrix(), 0, ',', ' ') ?> FCFA
                        </p>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>