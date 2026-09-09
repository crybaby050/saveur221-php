<?php

use Core\View;

$titrePage = 'Catégories';
$section = 'categories';
?>

<!-- Tiroir ajout/modification catégorie -->
<div id="tiroir-categorie" class="tiroir-overlay">
    <div class="tiroir-panneau">
        <div class="flex items-center justify-between border-b border-creme px-6 py-4">
            <p id="titre-tiroir-categorie" class="font-voice text-lg font-medium text-charbon">Nouvelle catégorie</p>
            <button type="button" data-fermer-tiroir class="text-gris-chaud hover:text-charbon">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                    <path d="M6 6l12 12M18 6 6 18" stroke-linecap="round"/>
                </svg>
            </button>
        </div>

        <div class="px-6 py-5">
            <img id="image-actuelle-categorie" src="" alt="" class="mb-4 hidden h-32 w-full rounded-lg object-cover">

            <form id="formulaire-tiroir-categorie" method="post" action="/gerant/categories/ajouter" enctype="multipart/form-data" class="flex flex-col gap-4">

                <div>
                    <label for="tiroir-nom" class="mb-1.5 block text-sm text-charbon">Nom</label>
                    <input type="text" id="tiroir-nom" name="nom" required
                        class="h-11 w-full rounded-md border border-creme bg-white px-3.5 text-sm text-charbon focus:border-bordeaux focus:outline-none focus:ring-3 focus:ring-bordeaux/10">
                </div>

                <div>
                    <label for="tiroir-description-categorie" class="mb-1.5 block text-sm text-charbon">Description (facultatif)</label>
                    <textarea id="tiroir-description-categorie" name="description" rows="3"
                        class="w-full rounded-md border border-creme bg-white px-3.5 py-2.5 text-sm text-charbon focus:border-bordeaux focus:outline-none focus:ring-3 focus:ring-bordeaux/10"></textarea>
                </div>

                <div class="flex gap-4">
                    <div class="flex-1">
                        <label for="tiroir-couleur" class="mb-1.5 block text-sm text-charbon">Couleur</label>
                        <input type="color" id="tiroir-couleur" name="couleur"
                            class="h-11 w-full cursor-pointer rounded-md border border-creme bg-white px-1.5">
                    </div>
                    <div class="flex-1">
                        <label for="tiroir-image-categorie" class="mb-1.5 block text-sm text-charbon">Image (facultatif)</label>
                        <input type="file" id="tiroir-image-categorie" name="image" accept="image/*"
                            class="w-full rounded-md border border-creme bg-white px-3 py-2 text-sm text-charbon file:mr-3 file:rounded file:border-0 file:bg-ivoire file:px-2.5 file:py-1 file:text-xs">
                    </div>
                </div>

                <div class="mt-2 flex justify-end gap-3">
                    <button type="button" data-fermer-tiroir class="rounded-md border border-creme px-4 py-2.5 text-sm font-medium text-charbon transition-colors hover:bg-ivoire">
                        Annuler
                    </button>
                    <button type="submit" class="rounded-md bg-bordeaux px-5 py-2.5 text-sm font-medium text-ivoire transition-colors hover:bg-bordeaux-sombre">
                        Enregistrer
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<script>
    const tiroirCategorieForm = document.getElementById('formulaire-tiroir-categorie');
    const tiroirCategorieTitre = document.getElementById('titre-tiroir-categorie');
    const tiroirCategorieImage = document.getElementById('image-actuelle-categorie');

    function ouvrirTiroirCategorieAjout() {
        tiroirCategorieForm.reset();
        tiroirCategorieForm.action = '/gerant/categories/ajouter';
        tiroirCategorieTitre.textContent = 'Nouvelle catégorie';
        tiroirCategorieImage.classList.add('hidden');
        document.getElementById('tiroir-categorie').classList.add('tiroir-ouvert');
    }

    function ouvrirTiroirCategorieModification(bouton) {
        tiroirCategorieForm.reset();
        tiroirCategorieForm.action = `/gerant/categories/${bouton.dataset.categorieId}/modifier`;
        tiroirCategorieForm.nom.value = bouton.dataset.categorieNom;
        tiroirCategorieForm.description.value = bouton.dataset.categorieDescription;
        tiroirCategorieForm.couleur.value = bouton.dataset.categorieCouleur;
        tiroirCategorieTitre.textContent = 'Modifier la catégorie';

        if (bouton.dataset.categorieImage) {
            tiroirCategorieImage.src = bouton.dataset.categorieImage;
            tiroirCategorieImage.classList.remove('hidden');
        } else {
            tiroirCategorieImage.classList.add('hidden');
        }

        document.getElementById('tiroir-categorie').classList.add('tiroir-ouvert');
    }
</script>

<div class="space-y-6">

    <!-- Barre d'action -->
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <form method="get" action="/gerant/categories" class="relative w-full sm:max-w-xs">
            <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gris-chaud" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                <circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3" stroke-linecap="round"/>
            </svg>
            <input
                type="text"
                name="recherche"
                value="<?= View::e($motCle ?? '') ?>"
                placeholder="Rechercher une catégorie..."
                class="h-10 w-full rounded-md border border-creme bg-white pl-9 pr-3 text-sm text-charbon focus:border-bordeaux focus:outline-none focus:ring-3 focus:ring-bordeaux/10"
            >
        </form>

        <div class="flex items-center gap-3">
            <?php include __DIR__ . '/../../partials/bascule-affichage.php'; ?>

            <button type="button" onclick="ouvrirTiroirCategorieAjout()"
                class="inline-flex items-center justify-center gap-2 rounded-md bg-bordeaux px-4 py-2.5 text-sm font-medium text-ivoire transition-colors hover:bg-bordeaux-sombre">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                    <path d="M12 5v14M5 12h14" stroke-linecap="round"/>
                </svg>
                Nouvelle catégorie
            </button>
        </div>
    </div>

    <?php if (isset($erreur)): ?>
        <p class="rounded-md border-l-4 border-danger bg-danger/10 px-3.5 py-2.5 text-sm text-danger">
            <?= View::e($erreur) ?>
        </p>
    <?php endif; ?>

    <?php if (empty($categories)): ?>
        <div class="rounded-xl border border-dashed border-creme bg-white p-10 text-center text-sm text-gris-chaud">
            Aucune catégorie trouvée.
        </div>
    <?php else: ?>

        <!-- Vue grille -->
        <div data-vue-contenu="grille" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            <?php foreach ($categories as $categorie): ?>
                <div class="group relative overflow-hidden rounded-xl border border-creme bg-white shadow-sm transition-shadow hover:shadow-md">
                    <div class="relative h-32 w-full overflow-hidden" style="background-color: <?= View::e($categorie->getCouleur() ?? '#EFEFF7') ?>1A;">
                        <img
                            src="<?= View::e($categorie->getImage() ?? '/assets/images/categorie-defaut.svg') ?>"
                            alt="<?= View::e($categorie->getNom()) ?>"
                            class="h-full w-full object-cover"
                        >
                        <span class="absolute left-3 top-3 h-4 w-4 rounded-full border-2 border-white shadow" style="background-color: <?= View::e($categorie->getCouleur() ?? '#8A8798') ?>;"></span>
                    </div>
                    <div class="p-4">
                        <p class="font-medium text-charbon"><?= View::e($categorie->getNom()) ?></p>
                        <?php if ($categorie->getDescription()): ?>
                            <p class="mt-1 line-clamp-2 text-xs text-gris-chaud"><?= View::e($categorie->getDescription()) ?></p>
                        <?php endif; ?>
                        <div class="mt-3 flex items-center gap-2">
                            <button type="button"
                                onclick="ouvrirTiroirCategorieModification(this)"
                                data-categorie-id="<?= $categorie->getId() ?>"
                                data-categorie-nom="<?= View::e($categorie->getNom()) ?>"
                                data-categorie-description="<?= View::e($categorie->getDescription() ?? '') ?>"
                                data-categorie-couleur="<?= View::e($categorie->getCouleur() ?? '#6C5CE7') ?>"
                                data-categorie-image="<?= View::e($categorie->getImage() ?? '') ?>"
                                class="flex-1 rounded-md border border-creme px-3 py-1.5 text-center text-xs font-medium text-charbon transition-colors hover:bg-ivoire">
                                Modifier
                            </button>
                            <form method="post" action="/gerant/categories/<?= $categorie->getId() ?>/supprimer" class="flex-1">
                                <button type="button"
                                    onclick="demanderSuppression(this.closest('form'), 'Supprimer « <?= View::e($categorie->getNom()) ?> » ?')"
                                    class="w-full rounded-md border border-danger/30 px-3 py-1.5 text-xs font-medium text-danger transition-colors hover:bg-danger/10">
                                    Supprimer
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Vue tableau -->
        <div data-vue-contenu="table" class="hidden overflow-hidden rounded-xl border border-creme bg-white shadow-sm">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-creme text-xs uppercase tracking-wide text-gris-chaud">
                        <th class="px-5 py-3 font-medium">Catégorie</th>
                        <th class="px-5 py-3 font-medium">Description</th>
                        <th class="px-5 py-3 font-medium text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-creme">
                    <?php foreach ($categories as $categorie): ?>
                        <tr class="hover:bg-ivoire">
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-3">
                                    <span class="h-3 w-3 shrink-0 rounded-full" style="background-color: <?= View::e($categorie->getCouleur() ?? '#8A8798') ?>;"></span>
                                    <span class="font-medium text-charbon"><?= View::e($categorie->getNom()) ?></span>
                                </div>
                            </td>
                            <td class="px-5 py-3 text-gris-chaud"><?= View::e($categorie->getDescription() ?? '—') ?></td>
                            <td class="px-5 py-3">
                                <div class="flex justify-end gap-2">
                                    <button type="button"
                                        onclick="ouvrirTiroirCategorieModification(this)"
                                        data-categorie-id="<?= $categorie->getId() ?>"
                                        data-categorie-nom="<?= View::e($categorie->getNom()) ?>"
                                        data-categorie-description="<?= View::e($categorie->getDescription() ?? '') ?>"
                                        data-categorie-couleur="<?= View::e($categorie->getCouleur() ?? '#6C5CE7') ?>"
                                        data-categorie-image="<?= View::e($categorie->getImage() ?? '') ?>"
                                        class="flex-1 rounded-md border border-creme px-3 py-1.5 text-center text-xs font-medium text-charbon transition-colors hover:bg-ivoire">
                                        Modifier
                                    </button>
                                    <form method="post" action="/gerant/categories/<?= $categorie->getId() ?>/supprimer">
                                        <button type="button"
                                            onclick="demanderSuppression(this.closest('form'), 'Supprimer « <?= View::e($categorie->getNom()) ?> » ?')"
                                            class="rounded-md border border-danger/30 px-3 py-1.5 text-xs font-medium text-danger transition-colors hover:bg-danger/10">
                                            Supprimer
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php
        $urlBase = '/gerant/categories';
        $parametres = $motCle !== null && $motCle !== '' ? ['recherche' => $motCle] : [];
        include __DIR__ . '/../../partials/pagination.php';
        ?>

    <?php endif; ?>

</div>