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

            <a href="/gerant/produits/stock"
                class="inline-flex items-center justify-center gap-2 rounded-md border border-creme px-4 py-2.5 text-sm font-medium text-charbon transition-colors hover:bg-ivoire"
            >
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                    <path d="M21 8 12 3 3 8l9 5 9-5Z"/><path d="M3 8v9l9 5 9-5V8"/>
                </svg>
                Suivi de stock
            </a>

            <a href="/gerant/produits/ajouter"
                class="inline-flex items-center justify-center gap-2 rounded-md bg-bordeaux px-4 py-2.5 text-sm font-medium text-ivoire transition-colors hover:bg-bordeaux-sombre"
            >
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                    <path d="M12 5v14M5 12h14" stroke-linecap="round"/>
                </svg>
                Nouveau produit
            </a>
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
                            <a href="/gerant/produits/<?= $produit->getId() ?>/modifier" class="flex-1 rounded-md border border-creme px-3 py-1.5 text-center text-xs font-medium text-charbon transition-colors hover:bg-ivoire">
                                Modifier
                            </a>
                            <form method="post" action="/gerant/produits/<?= $produit->getId() ?>/supprimer" class="flex-1" onsubmit="return confirm('Supprimer ce produit ?');">
                                <button type="submit" class="w-full rounded-md border border-danger/30 px-3 py-1.5 text-xs font-medium text-danger transition-colors hover:bg-danger/10">
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
                                    <a href="/gerant/produits/<?= $produit->getId() ?>/modifier" class="rounded-md border border-creme px-3 py-1.5 text-xs font-medium text-charbon transition-colors hover:bg-ivoire">
                                        Modifier
                                    </a>
                                    <form method="post" action="/gerant/produits/<?= $produit->getId() ?>/supprimer" onsubmit="return confirm('Supprimer ce produit ?');">
                                        <button type="submit" class="rounded-md border border-danger/30 px-3 py-1.5 text-xs font-medium text-danger transition-colors hover:bg-danger/10">
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