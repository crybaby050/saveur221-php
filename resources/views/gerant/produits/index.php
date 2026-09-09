<?php

use Core\View;

$titrePage = 'Produits & stock';
$section = 'produits';

$motCle = $_GET['recherche'] ?? '';
$categorieActive = isset($_GET['categorie']) ? (int) $_GET['categorie'] : null;

$nomsCategories = [];
foreach ($categories as $categorie) {
    $nomsCategories[$categorie->getId()] = $categorie->getNom();
}

function badge_stock(\App\Models\Produit $produit): array
{
    return match (true) {
        $produit->estEnRupture() => ['Rupture', 'bg-danger/10 text-danger border-danger/30'],
        $produit->estStockFaible() => ['Stock faible', 'bg-or/10 text-or-clair border-or/30'],
        default => ['En stock', 'bg-succes/10 text-succes border-succes/30'],
    };
}
?>

<!-- Tiroir ajout/modification produit -->
<div id="tiroir-produit" class="tiroir-overlay">
    <div class="tiroir-panneau">
        <div class="flex items-center justify-between border-b border-creme px-6 py-4">
            <p id="titre-tiroir-produit" class="font-voice text-lg font-medium text-charbon">Nouveau produit</p>
            <button type="button" data-fermer-tiroir class="text-gris-chaud hover:text-charbon">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                    <path d="M6 6l12 12M18 6 6 18" stroke-linecap="round"/>
                </svg>
            </button>
        </div>

        <div class="px-6 py-5">
            <img id="image-actuelle-produit" src="" alt="" class="mb-4 hidden h-32 w-full rounded-lg object-cover">

            <form id="formulaire-tiroir-produit" method="post" action="/gerant/produits/ajouter" enctype="multipart/form-data" class="space-y-5">

                <div>
                    <label for="tiroir-libelle" class="mb-1.5 block text-sm font-medium text-charbon">Libellé</label>
                    <input type="text" id="tiroir-libelle" name="libelle" required
                        class="h-10 w-full rounded-md border border-creme bg-white px-3 text-sm text-charbon focus:border-bordeaux focus:outline-none focus:ring-3 focus:ring-bordeaux/10">
                </div>

                <div>
                    <label for="tiroir-description" class="mb-1.5 block text-sm font-medium text-charbon">Description</label>
                    <textarea id="tiroir-description" name="description" rows="3"
                        class="w-full rounded-md border border-creme bg-white px-3 py-2 text-sm text-charbon focus:border-bordeaux focus:outline-none focus:ring-3 focus:ring-bordeaux/10"></textarea>
                </div>

                <div>
                    <label for="tiroir-prix" class="mb-1.5 block text-sm font-medium text-charbon">Prix (FCFA)</label>
                    <input type="number" id="tiroir-prix" name="prix" min="0" step="1" required
                        class="h-10 w-full rounded-md border border-creme bg-white px-3 text-sm text-charbon focus:border-bordeaux focus:outline-none focus:ring-3 focus:ring-bordeaux/10">
                </div>

                <div>
                    <label for="tiroir-categorie" class="mb-1.5 block text-sm font-medium text-charbon">Catégorie</label>
                    <select id="tiroir-categorie" name="categorie_id" required
                        class="h-10 w-full rounded-md border border-creme bg-white px-3 text-sm text-charbon focus:border-bordeaux focus:outline-none focus:ring-3 focus:ring-bordeaux/10">
                        <option value="">Choisir...</option>
                        <?php foreach ($categories as $categorie): ?>
                            <option value="<?= $categorie->getId() ?>"><?= View::e($categorie->getNom()) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div id="champs-creation-produit" class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div>
                        <label for="tiroir-quantite-stock" class="mb-1.5 block text-sm font-medium text-charbon">Stock initial</label>
                        <input type="number" id="tiroir-quantite-stock" name="quantite_stock" min="0" value="0"
                            class="h-10 w-full rounded-md border border-creme bg-white px-3 text-sm text-charbon focus:border-bordeaux focus:outline-none focus:ring-3 focus:ring-bordeaux/10">
                    </div>
                    <div>
                        <label for="tiroir-seuil-alerte" class="mb-1.5 block text-sm font-medium text-charbon">Seuil d'alerte</label>
                        <input type="number" id="tiroir-seuil-alerte" name="seuil_alerte" min="0" value="5"
                            class="h-10 w-full rounded-md border border-creme bg-white px-3 text-sm text-charbon focus:border-bordeaux focus:outline-none focus:ring-3 focus:ring-bordeaux/10">
                    </div>
                </div>

                <div>
                    <label for="tiroir-image" class="mb-1.5 block text-sm font-medium text-charbon">Image (facultatif)</label>
                    <input type="file" id="tiroir-image" name="image" accept="image/*"
                        class="block w-full text-sm text-gris-chaud file:mr-3 file:rounded-md file:border-0 file:bg-ivoire file:px-3 file:py-2 file:text-sm file:font-medium file:text-charbon hover:file:bg-creme">
                </div>

                <div class="flex justify-end gap-3 pt-2">
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
    const tiroirProduitForm = document.getElementById('formulaire-tiroir-produit');
    const tiroirProduitTitre = document.getElementById('titre-tiroir-produit');
    const tiroirProduitImage = document.getElementById('image-actuelle-produit');
    const champsCreationProduit = document.getElementById('champs-creation-produit');

    function ouvrirTiroirProduitAjout() {
        tiroirProduitForm.reset();
        tiroirProduitForm.action = '/gerant/produits/ajouter';
        tiroirProduitTitre.textContent = 'Nouveau produit';
        tiroirProduitImage.classList.add('hidden');
        champsCreationProduit.classList.remove('hidden');
        document.getElementById('tiroir-produit').classList.add('tiroir-ouvert');
    }

    function ouvrirTiroirProduitModification(bouton) {
        tiroirProduitForm.reset();
        tiroirProduitForm.action = `/gerant/produits/${bouton.dataset.produitId}/modifier`;
        tiroirProduitForm.libelle.value = bouton.dataset.produitLibelle;
        tiroirProduitForm.description.value = bouton.dataset.produitDescription;
        tiroirProduitForm.prix.value = bouton.dataset.produitPrix;
        tiroirProduitForm.categorie_id.value = bouton.dataset.produitCategorieId;
        tiroirProduitTitre.textContent = 'Modifier le produit';
        champsCreationProduit.classList.add('hidden');

        if (bouton.dataset.produitImage) {
            tiroirProduitImage.src = bouton.dataset.produitImage;
            tiroirProduitImage.classList.remove('hidden');
        } else {
            tiroirProduitImage.classList.add('hidden');
        }

        document.getElementById('tiroir-produit').classList.add('tiroir-ouvert');
    }
</script>

<!-- Tiroir suivi de stock -->
<div id="tiroir-stock" class="tiroir-overlay">
    <div class="tiroir-panneau">
        <div class="flex items-center justify-between border-b border-creme px-6 py-4">
            <p class="font-voice text-lg font-medium text-charbon">Suivi de stock</p>
            <button type="button" data-fermer-tiroir class="text-gris-chaud hover:text-charbon">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                    <path d="M6 6l12 12M18 6 6 18" stroke-linecap="round"/>
                </svg>
            </button>
        </div>

        <div class="px-6 py-5">
            <?php include __DIR__ . '/../../partials/contenu-stock.php'; ?>
        </div>
    </div>
</div>

<script>
    function ouvrirTiroirStock() {
        document.getElementById('tiroir-stock').classList.add('tiroir-ouvert');
    }
                            
        function ouvrirTiroirStock() {
        document.getElementById('tiroir-stock').classList.add('tiroir-ouvert');
    }
                            
    if (new URLSearchParams(window.location.search).get('stock') === '1') {
        ouvrirTiroirStock();
    }
</script>

<div class="space-y-6">

    <!-- Barre d'action -->
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <form method="get" action="/gerant/produits" class="flex flex-1 flex-col gap-3 sm:flex-row sm:items-center">
            <div class="relative w-full sm:max-w-xs">
                <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gris-chaud" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                    <circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3" stroke-linecap="round"/>
                </svg>
                <input
                    type="text"
                    name="recherche"
                    value="<?= View::e($motCle) ?>"
                    placeholder="Rechercher un produit..."
                    class="h-10 w-full rounded-md border border-creme bg-white pl-9 pr-3 text-sm text-charbon focus:border-bordeaux focus:outline-none focus:ring-3 focus:ring-bordeaux/10"
                >
            </div>

            <select
                name="categorie"
                onchange="this.form.submit()"
                class="h-10 rounded-md border border-creme bg-white px-3 text-sm text-charbon focus:border-bordeaux focus:outline-none focus:ring-3 focus:ring-bordeaux/10"
            >
                <option value="">Toutes les catégories</option>
                <?php foreach ($categories as $categorie): ?>
                    <option value="<?= $categorie->getId() ?>" <?= $categorieActive === $categorie->getId() ? 'selected' : '' ?>>
                        <?= View::e($categorie->getNom()) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>

        <div class="flex items-center gap-3">
            <?php include __DIR__ . '/../../partials/bascule-affichage.php'; ?>

            <button type="button" onclick="ouvrirTiroirStock()"
                class="inline-flex items-center justify-center gap-2 rounded-md border border-creme px-4 py-2.5 text-sm font-medium text-charbon transition-colors hover:bg-ivoire"
            >
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                    <path d="M21 8 12 3 3 8l9 5 9-5Z"/><path d="M3 8v9l9 5 9-5V8"/>
                </svg>
                Suivi de stock
            </button>

            <button type="button" onclick="ouvrirTiroirProduitAjout()"
                class="inline-flex items-center justify-center gap-2 rounded-md bg-bordeaux px-4 py-2.5 text-sm font-medium text-ivoire transition-colors hover:bg-bordeaux-sombre">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                    <path d="M12 5v14M5 12h14" stroke-linecap="round"/>
                </svg>
                Nouveau produit
            </button>
        </div>
    </div>

    <?php if (isset($erreur)): ?>
        <p class="rounded-md border-l-4 border-danger bg-danger/10 px-3.5 py-2.5 text-sm text-danger">
            <?= View::e($erreur) ?>
        </p>
    <?php endif; ?>

    <?php if (empty($produits)): ?>
        <div class="rounded-xl border border-dashed border-creme bg-white p-10 text-center text-sm text-gris-chaud">
            Aucun produit trouvé.
        </div>
    <?php else: ?>

        <!-- Vue grille -->
        <div data-vue-contenu="grille" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            <?php foreach ($produits as $produit): ?>
                <?php [$libelleBadge, $classeBadge] = badge_stock($produit); ?>
                <div class="group relative overflow-hidden rounded-xl border border-creme bg-white shadow-sm transition-shadow hover:shadow-md">
                    <div class="relative h-32 w-full overflow-hidden bg-ivoire">
                        <img
                            src="<?= View::e($produit->getImage() ?? '/assets/images/produit-defaut.svg') ?>"
                            alt="<?= View::e($produit->getLibelle()) ?>"
                            class="h-full w-full object-cover"
                        >
                        <span class="absolute left-3 top-3 rounded-full border px-2 py-0.5 text-[11px] font-medium <?= $classeBadge ?>">
                            <?= $libelleBadge ?>
                        </span>
                    </div>
                    <div class="p-4">
                        <p class="text-xs text-gris-chaud"><?= View::e($nomsCategories[$produit->getCategorieId()] ?? '—') ?></p>
                        <p class="mt-0.5 font-medium text-charbon"><?= View::e($produit->getLibelle()) ?></p>
                        <p class="mt-1 text-sm font-medium text-bordeaux"><?= number_format($produit->getPrix(), 0, ',', ' ') ?> FCFA</p>
                        <p class="mt-1 text-xs text-gris-chaud"><?= $produit->getQuantiteStock() ?> en stock</p>

                        <div class="mt-3 flex items-center gap-2">
                            <button type="button"
                                onclick="ouvrirTiroirProduitModification(this)"
                                data-produit-id="<?= $produit->getId() ?>"
                                data-produit-libelle="<?= View::e($produit->getLibelle()) ?>"
                                data-produit-description="<?= View::e($produit->getDescription() ?? '') ?>"
                                data-produit-prix="<?= $produit->getPrix() ?>"
                                data-produit-categorie-id="<?= $produit->getCategorieId() ?>"
                                data-produit-image="<?= View::e($produit->getImage() ?? '') ?>"
                                class="flex-1 rounded-md border border-creme px-3 py-1.5 text-center text-xs font-medium text-charbon transition-colors hover:bg-ivoire">
                                Modifier
                            </button>
                            <form method="post" action="/gerant/produits/<?= $produit->getId() ?>/supprimer" class="flex-1">
    <button
        type="button"
        onclick="demanderSuppression(this.closest('form'), 'Supprimer « <?= View::e($produit->getLibelle()) ?> » ?')"
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
        <div data-vue-contenu="table" class="hidden overflow-x-auto rounded-xl border border-creme bg-white shadow-sm">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-creme text-xs uppercase tracking-wide text-gris-chaud">
                        <th class="px-5 py-3 font-medium">Produit</th>
                        <th class="px-5 py-3 font-medium">Catégorie</th>
                        <th class="px-5 py-3 font-medium">Prix</th>
                        <th class="px-5 py-3 font-medium">Stock</th>
                        <th class="px-5 py-3 font-medium">Statut</th>
                        <th class="px-5 py-3 font-medium text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-creme">
                    <?php foreach ($produits as $produit): ?>
                        <?php [$libelleBadge, $classeBadge] = badge_stock($produit); ?>
                        <tr class="hover:bg-ivoire">
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-3">
                                    <img src="<?= View::e($produit->getImage() ?? '/assets/images/produit-defaut.svg') ?>" alt="" class="h-9 w-9 shrink-0 rounded-md object-cover">
                                    <span class="font-medium text-charbon"><?= View::e($produit->getLibelle()) ?></span>
                                </div>
                            </td>
                            <td class="px-5 py-3 text-gris-chaud"><?= View::e($nomsCategories[$produit->getCategorieId()] ?? '—') ?></td>
                            <td class="px-5 py-3 text-charbon"><?= number_format($produit->getPrix(), 0, ',', ' ') ?> FCFA</td>
                            <td class="px-5 py-3 text-charbon"><?= $produit->getQuantiteStock() ?></td>
                            <td class="px-5 py-3">
                                <span class="rounded-full border px-2 py-0.5 text-xs font-medium <?= $classeBadge ?>"><?= $libelleBadge ?></span>
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex justify-end gap-2">
                                    <button type="button"
                                        onclick="ouvrirTiroirProduitModification(this)"
                                        data-produit-id="<?= $produit->getId() ?>"
                                        data-produit-libelle="<?= View::e($produit->getLibelle()) ?>"
                                        data-produit-description="<?= View::e($produit->getDescription() ?? '') ?>"
                                        data-produit-prix="<?= $produit->getPrix() ?>"
                                        data-produit-categorie-id="<?= $produit->getCategorieId() ?>"
                                        data-produit-image="<?= View::e($produit->getImage() ?? '') ?>"
                                        class="flex-1 rounded-md border border-creme px-3 py-1.5 text-center text-xs font-medium text-charbon transition-colors hover:bg-ivoire">
                                        Modifier
                                    </button>
                                    <form method="post" action="/gerant/produits/<?= $produit->getId() ?>/supprimer" class="flex-1">
                                    <button type="button"
                                        onclick="demanderSuppression(this.closest('form'), 'Supprimer « <?= View::e($produit->getLibelle()) ?> » ?')"
                                        class="w-full rounded-md border border-danger/30 px-3 py-1.5 text-xs font-medium text-danger transition-colors hover:bg-danger/10">
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

    <?php endif; ?>

</div>