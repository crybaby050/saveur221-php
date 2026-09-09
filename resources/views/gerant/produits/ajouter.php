<?php

use Core\View;

$titrePage = 'Nouveau produit';
$section = 'produits';
?>

<div class="mx-auto max-w-2xl space-y-6">

    <a href="/gerant/produits" class="inline-flex items-center gap-1.5 text-sm text-gris-chaud transition-colors hover:text-charbon">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
            <path d="M15 18l-6-6 6-6" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        Retour aux produits
    </a>

    <div class="rounded-xl border border-creme bg-white p-6 shadow-sm">
        <?php if (isset($erreur)): ?>
            <p class="mb-4 rounded-md border-l-4 border-danger bg-danger/10 px-3.5 py-2.5 text-sm text-danger">
                <?= View::e($erreur) ?>
            </p>
        <?php endif; ?>

        <form method="post" action="/gerant/produits/ajouter" enctype="multipart/form-data" class="space-y-5">

            <div>
                <label for="libelle" class="mb-1.5 block text-sm font-medium text-charbon">Libellé</label>
                <input type="text" id="libelle" name="libelle" required
                    class="h-10 w-full rounded-md border border-creme bg-white px-3 text-sm text-charbon focus:border-bordeaux focus:outline-none focus:ring-3 focus:ring-bordeaux/10">
            </div>

            <div>
                <label for="description" class="mb-1.5 block text-sm font-medium text-charbon">Description</label>
                <textarea id="description" name="description" rows="3"
                    class="w-full rounded-md border border-creme bg-white px-3 py-2 text-sm text-charbon focus:border-bordeaux focus:outline-none focus:ring-3 focus:ring-bordeaux/10"></textarea>
            </div>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <label for="prix" class="mb-1.5 block text-sm font-medium text-charbon">Prix (FCFA)</label>
                    <input type="number" id="prix" name="prix" min="0" step="1" required
                        class="h-10 w-full rounded-md border border-creme bg-white px-3 text-sm text-charbon focus:border-bordeaux focus:outline-none focus:ring-3 focus:ring-bordeaux/10">
                </div>
                <div>
                    <label for="categorie_id" class="mb-1.5 block text-sm font-medium text-charbon">Catégorie</label>
                    <select id="categorie_id" name="categorie_id" required
                        class="h-10 w-full rounded-md border border-creme bg-white px-3 text-sm text-charbon focus:border-bordeaux focus:outline-none focus:ring-3 focus:ring-bordeaux/10">
                        <option value="">Choisir...</option>
                        <?php foreach ($categories as $categorie): ?>
                            <option value="<?= $categorie->getId() ?>"><?= View::e($categorie->getNom()) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <label for="quantite_stock" class="mb-1.5 block text-sm font-medium text-charbon">Stock initial</label>
                    <input type="number" id="quantite_stock" name="quantite_stock" min="0" value="0" required
                        class="h-10 w-full rounded-md border border-creme bg-white px-3 text-sm text-charbon focus:border-bordeaux focus:outline-none focus:ring-3 focus:ring-bordeaux/10">
                </div>
                <div>
                    <label for="seuil_alerte" class="mb-1.5 block text-sm font-medium text-charbon">Seuil d'alerte</label>
                    <input type="number" id="seuil_alerte" name="seuil_alerte" min="0" value="5" required
                        class="h-10 w-full rounded-md border border-creme bg-white px-3 text-sm text-charbon focus:border-bordeaux focus:outline-none focus:ring-3 focus:ring-bordeaux/10">
                </div>
            </div>

            <div>
                <label for="image" class="mb-1.5 block text-sm font-medium text-charbon">Image (facultatif)</label>
                <input type="file" id="image" name="image" accept="image/*"
                    class="block w-full text-sm text-gris-chaud file:mr-3 file:rounded-md file:border-0 file:bg-ivoire file:px-3 file:py-2 file:text-sm file:font-medium file:text-charbon hover:file:bg-creme">
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <a href="/gerant/produits" class="rounded-md border border-creme px-4 py-2.5 text-sm font-medium text-charbon transition-colors hover:bg-ivoire">
                    Annuler
                </a>
                <button type="submit" class="rounded-md bg-bordeaux px-5 py-2.5 text-sm font-medium text-ivoire transition-colors hover:bg-bordeaux-sombre">
                    Créer le produit
                </button>
            </div>

        </form>
    </div>
</div>