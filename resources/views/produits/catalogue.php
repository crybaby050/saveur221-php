<?php

use Core\View;

$titrePage = 'Menu';
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
                Commander maintenant →
            </a>
        </div>
        <div class="relative flex h-full flex-1 items-end justify-center">
            <img src="https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=1200&q=80" alt="" class="h-[205px] w-full object-cover">
        </div>
    </div>
</section>

<!-- Catégories -->
<?php if (!empty($categories)): ?>
    <section class="mt-5">
        <div class="hide-scrollbar flex gap-3 overflow-x-auto pb-2">
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
    </section>
<?php endif; ?>

<!-- Menu -->
<section class="mt-5">
    <div class="flex items-center justify-between">
        <h2 class="text-sm font-black text-charbon">Notre menu</h2>
    </div>

    <?php if (empty($produits)): ?>
        <p class="mt-4 text-sm text-gris-chaud">Aucun produit trouvé.</p>
    <?php else: ?>
        <div class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
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