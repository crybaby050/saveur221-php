<?php

use Core\View;

$titrePage = 'Catégories';
$section = 'categories';
?>

<div class="space-y-6">

    <!-- Barre d'action : recherche + ajout -->
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <form method="get" action="/gerant/categories" class="relative w-full sm:max-w-xs">
            <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gris-chaud" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                <circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3" stroke-linecap="round"/>
            </svg>
            <input
                type="text"
                name="recherche"
                value="<?= View::e($_GET['recherche'] ?? '') ?>"
                placeholder="Rechercher une catégorie..."
                class="h-10 w-full rounded-md border border-creme bg-white pl-9 pr-3 text-sm text-charbon focus:border-bordeaux focus:outline-none focus:ring-3 focus:ring-bordeaux/10"
            >
        </form>

        
        <a href="/gerant/categories/ajouter"
            class="inline-flex items-center justify-center gap-2 rounded-md bg-bordeaux px-4 py-2.5 text-sm font-medium text-ivoire transition-colors hover:bg-bordeaux-sombre"
        >
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                <path d="M12 5v14M5 12h14" stroke-linecap="round"/>
            </svg>
            Nouvelle catégorie
        </a>
    </div>

    <?php if (isset($erreur)): ?>
        <p class="rounded-md border-l-4 border-danger bg-danger/10 px-3.5 py-2.5 text-sm text-danger">
            <?= View::e($erreur) ?>
        </p>
    <?php endif; ?>

    <!-- Grille de catégories -->
    <?php if (empty($categories)): ?>
        <div class="rounded-xl border border-dashed border-creme bg-white p-10 text-center text-sm text-gris-chaud">
            Aucune catégorie trouvée.
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            <?php foreach ($categories as $categorie): ?>
                <div class="group relative overflow-hidden rounded-xl border border-creme bg-white shadow-sm transition-shadow hover:shadow-md">

                    <div class="relative h-32 w-full overflow-hidden" style="background-color: <?= View::e($categorie->getCouleur() ?? '#EFEFF7') ?>1A;">
                        <img
                            src="<?= View::e($categorie->getImage() ?? '/assets/images/categorie-defaut.svg') ?>"
                            alt="<?= View::e($categorie->getNom()) ?>"
                            class="h-full w-full object-cover"
                        >
                        <span
                            class="absolute left-3 top-3 h-4 w-4 rounded-full border-2 border-white shadow"
                            style="background-color: <?= View::e($categorie->getCouleur() ?? '#8A8798') ?>;"
                        ></span>
                    </div>

                    <div class="p-4">
                        <p class="font-medium text-charbon"><?= View::e($categorie->getNom()) ?></p>
                        <?php if ($categorie->getDescription()): ?>
                            <p class="mt-1 line-clamp-2 text-xs text-gris-chaud"><?= View::e($categorie->getDescription()) ?></p>
                        <?php endif; ?>

                        <div class="mt-3 flex items-center gap-2">
                            
                            <a href="/gerant/categories/<?= $categorie->getId() ?>/modifier"
                                class="flex-1 rounded-md border border-creme px-3 py-1.5 text-center text-xs font-medium text-charbon transition-colors hover:bg-ivoire"
                            >
                                Modifier
                            </a>
                            <form method="post" action="/gerant/categories/<?= $categorie->getId() ?>/supprimer" class="flex-1" onsubmit="return confirm('Supprimer cette catégorie ?');">
                                <button
                                    type="submit"
                                    class="w-full rounded-md border border-danger/30 px-3 py-1.5 text-xs font-medium text-danger transition-colors hover:bg-danger/10"
                                >
                                    Supprimer
                                </button>
                            </form>
                        </div>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>