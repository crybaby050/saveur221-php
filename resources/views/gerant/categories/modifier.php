<?php

use Core\View;

$titrePage = 'Modifier la catégorie';
$section = 'categories';
?>

<div class="mx-auto max-w-xl">
    <div class="rounded-xl border border-creme bg-white p-6 shadow-sm">

        <?php if ($categorie->getImage()): ?>
            <img src="<?= View::e($categorie->getImage()) ?>" alt="" class="mb-5 h-32 w-full rounded-lg object-cover">
        <?php endif; ?>

        <form method="post" action="/gerant/categories/<?= $categorie->getId() ?>/modifier" enctype="multipart/form-data" class="flex flex-col gap-4">

            <div>
                <label for="nom" class="mb-1.5 block text-sm text-charbon">Nom</label>
                <input
                    type="text"
                    id="nom"
                    name="nom"
                    value="<?= View::e($categorie->getNom()) ?>"
                    required
                    class="h-11 w-full rounded-md border border-creme bg-white px-3.5 text-sm text-charbon focus:border-bordeaux focus:outline-none focus:ring-3 focus:ring-bordeaux/10"
                >
            </div>

            <div>
                <label for="description" class="mb-1.5 block text-sm text-charbon">Description (facultatif)</label>
                <textarea
                    id="description"
                    name="description"
                    rows="3"
                    class="w-full rounded-md border border-creme bg-white px-3.5 py-2.5 text-sm text-charbon focus:border-bordeaux focus:outline-none focus:ring-3 focus:ring-bordeaux/10"
                ><?= View::e($categorie->getDescription() ?? '') ?></textarea>
            </div>

            <div class="flex gap-4">
                <div class="flex-1">
                    <label for="couleur" class="mb-1.5 block text-sm text-charbon">Couleur</label>
                    <input
                        type="color"
                        id="couleur"
                        name="couleur"
                        value="<?= View::e($categorie->getCouleur() ?? '#6C5CE7') ?>"
                        class="h-11 w-full cursor-pointer rounded-md border border-creme bg-white px-1.5"
                    >
                </div>

                <div class="flex-1">
                    <label for="image" class="mb-1.5 block text-sm text-charbon">Nouvelle image (facultatif)</label>
                    <input
                        type="file"
                        id="image"
                        name="image"
                        accept="image/*"
                        class="w-full rounded-md border border-creme bg-white px-3 py-2 text-sm text-charbon file:mr-3 file:rounded file:border-0 file:bg-ivoire file:px-2.5 file:py-1 file:text-xs"
                    >
                </div>
            </div>

            <div class="mt-2 flex items-center gap-3">
                <button
                    type="submit"
                    class="rounded-md bg-bordeaux px-5 py-2.5 text-sm font-medium text-ivoire transition-colors hover:bg-bordeaux-sombre"
                >
                    Enregistrer
                </button>
                <a href="/gerant/categories" class="text-sm text-gris-chaud hover:text-charbon">Annuler</a>
            </div>

        </form>

    </div>
</div>