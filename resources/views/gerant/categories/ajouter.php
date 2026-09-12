<?php

use Core\View;

$titrePage = 'Nouvelle catégorie';
$section = 'categories';
?>

<div class="mx-auto max-w-xl">
    <div class="rounded-xl border border-creme bg-white p-6 shadow-sm">

        <form method="post" action="/gerant/categories/ajouter" enctype="multipart/form-data" class="flex flex-col gap-4">

            <div>
                <label for="nom" class="mb-1.5 block text-sm text-charbon">Nom</label>
                <input
                    type="text"
                    id="nom"
                    name="nom"
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
                ></textarea>
            </div>

            <div class="flex gap-4">
                <div class="flex-1">
                    <label for="couleur" class="mb-1.5 block text-sm text-charbon">Couleur (facultatif)</label>
                    <input
                        type="color"
                        id="couleur"
                        name="couleur"
                        class="h-11 w-full cursor-pointer rounded-md border border-creme bg-white px-1.5"
                    >
                    <p class="mt-1 text-xs text-gris-chaud">Laisser vide pour une couleur générée automatiquement.</p>
                </div>

                <div class="flex-1">
                    <label for="image" class="mb-1.5 block text-sm text-charbon">Image (facultatif)</label>
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
                    Créer la catégorie
                </button>
                <a href="/gerant/categories" class="text-sm text-gris-chaud hover:text-charbon">Annuler</a>
            </div>

        </form>

    </div>
</div>