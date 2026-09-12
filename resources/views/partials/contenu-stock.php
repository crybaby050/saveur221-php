<?php

use Core\View;

/*
 * Contenu du suivi de stock (résumé + listes), réutilisé à la fois par la
 * page complète gerant/produits/stock.php et par le tiroir ouvert depuis
 * gerant/produits/index.php. Attend $produits, $stockFaible, $ruptures
 * (et optionnellement $erreur) dans la portée appelante.
 */
$produits ??= [];
$stockFaible ??= [];
$ruptures ??= [];
?>

<!-- Résumé -->
<div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
    <div class="rounded-xl border border-danger/20 bg-danger/5 p-5">
        <p class="text-xs font-medium uppercase tracking-wide text-danger">Ruptures de stock</p>
        <p class="mt-1 font-voice text-2xl font-medium text-charbon"><?= count($ruptures) ?></p>
    </div>
    <div class="rounded-xl border border-or/20 bg-or/5 p-5">
        <p class="text-xs font-medium uppercase tracking-wide text-or-clair">Stock faible</p>
        <p class="mt-1 font-voice text-2xl font-medium text-charbon"><?= count($stockFaible) ?></p>
    </div>
</div>

<?php if (isset($erreur)): ?>
    <p class="mt-4 rounded-md border-l-4 border-danger bg-danger/10 px-3.5 py-2.5 text-sm text-danger">
        <?= View::e($erreur) ?>
    </p>
<?php endif; ?>

<!-- Recherche et filtre -->
<div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center">
    <div class="relative flex-1">
        <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gris-chaud" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
            <circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3" stroke-linecap="round"/>
        </svg>
        <input
            type="text"
            id="recherche-stock"
            placeholder="Rechercher un produit..."
            class="h-10 w-full rounded-md border border-creme bg-white pl-9 pr-3 text-sm text-charbon focus:border-bordeaux focus:outline-none focus:ring-3 focus:ring-bordeaux/10"
        >
    </div>
    <select
        id="filtre-statut-stock"
        class="h-10 rounded-md border border-creme bg-white px-3 text-sm text-charbon focus:border-bordeaux focus:outline-none focus:ring-3 focus:ring-bordeaux/10"
    >
        <option value="">Tous les statuts</option>
        <option value="rupture">Rupture</option>
        <option value="faible">Stock faible</option>
        <option value="normal">En stock</option>
    </select>
</div>

<?php if (!empty($ruptures)): ?>
    <div data-section-stock class="mt-4 rounded-xl border border-creme bg-white shadow-sm">
        <div class="border-b border-creme px-5 py-3">
            <p class="text-sm font-medium text-charbon">Produits en rupture</p>
        </div>
        <div class="divide-y divide-creme">
            <?php foreach ($ruptures as $produit): ?>
                <?php include __DIR__ . '/ligne-stock.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<?php if (!empty($stockFaible)): ?>
    <div data-section-stock class="mt-4 rounded-xl border border-creme bg-white shadow-sm">
        <div class="border-b border-creme px-5 py-3">
            <p class="text-sm font-medium text-charbon">Stock faible</p>
        </div>
        <div class="divide-y divide-creme">
            <?php foreach ($stockFaible as $produit): ?>
                <?php include __DIR__ . '/ligne-stock.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<div data-section-stock class="mt-4 rounded-xl border border-creme bg-white shadow-sm">
    <div class="border-b border-creme px-5 py-3">
        <p class="text-sm font-medium text-charbon">Tous les produits</p>
    </div>
    <div class="divide-y divide-creme">
        <?php foreach ($produits as $produit): ?>
            <?php include __DIR__ . '/ligne-stock.php'; ?>
        <?php endforeach; ?>
    </div>
</div>

<!-- Modales de confirmation pour approvisionnement et seuil d'alerte -->
<div id="modal-approvisionnement" class="fixed inset-0 z-50 hidden items-center justify-center bg-charbon/50 p-4">
    <div class="w-full max-w-sm rounded-xl bg-white p-6 shadow-xl">
        <p class="font-voice text-lg font-medium text-charbon">Confirmer l'approvisionnement</p>
        <p id="modal-approvisionnement-message" class="mt-2 text-sm text-gris-chaud"></p>
        <div class="mt-5 flex justify-end gap-3">
            <button type="button" id="modal-approvisionnement-annuler"
                class="rounded-md border border-creme px-4 py-2 text-sm font-medium text-charbon transition-colors hover:bg-ivoire">
                Annuler
            </button>
            <button type="button" id="modal-approvisionnement-confirmer"
                class="rounded-md bg-bordeaux px-4 py-2 text-sm font-medium text-ivoire transition-colors hover:bg-bordeaux-sombre">
                Approvisionner
            </button>
        </div>
    </div>
</div>

<div id="modal-seuil" class="fixed inset-0 z-50 hidden items-center justify-center bg-charbon/50 p-4">
    <div class="w-full max-w-sm rounded-xl bg-white p-6 shadow-xl">
        <p class="font-voice text-lg font-medium text-charbon">Confirmer le nouveau seuil</p>
        <p id="modal-seuil-message" class="mt-2 text-sm text-gris-chaud"></p>
        <div class="mt-5 flex justify-end gap-3">
            <button type="button" id="modal-seuil-annuler"
                class="rounded-md border border-creme px-4 py-2 text-sm font-medium text-charbon transition-colors hover:bg-ivoire">
                Annuler
            </button>
            <button type="button" id="modal-seuil-confirmer"
                class="rounded-md bg-bordeaux px-4 py-2 text-sm font-medium text-ivoire transition-colors hover:bg-bordeaux-sombre">
                Redéfinir
            </button>
        </div>
    </div>
</div>

<script>
    let formulaireAApprovisionner = null;
    let formulaireASeuiler = null;

    function demanderApprovisionnement(formulaire, nomProduit) {
        formulaireAApprovisionner = formulaire;
        const quantite = formulaire.quantite.value;
        document.getElementById('modal-approvisionnement-message').textContent =
            `Ajouter ${quantite} unité(s) au stock de « ${nomProduit} » ?`;
        document.getElementById('modal-approvisionnement').classList.remove('hidden');
        document.getElementById('modal-approvisionnement').classList.add('flex');
    }

    function fermerModalApprovisionnement() {
        formulaireAApprovisionner = null;
        document.getElementById('modal-approvisionnement').classList.add('hidden');
        document.getElementById('modal-approvisionnement').classList.remove('flex');
    }

    document.getElementById('modal-approvisionnement-annuler').addEventListener('click', fermerModalApprovisionnement);
    document.getElementById('modal-approvisionnement-confirmer').addEventListener('click', () => {
        if (formulaireAApprovisionner) {
            formulaireAApprovisionner.submit();
        }
    });

    function demanderChangementSeuil(formulaire, nomProduit) {
        formulaireASeuiler = formulaire;
        const seuil = formulaire.seuil.value;
        document.getElementById('modal-seuil-message').textContent =
            `Redéfinir le seuil d'alerte de « ${nomProduit} » à ${seuil} ?`;
        document.getElementById('modal-seuil').classList.remove('hidden');
        document.getElementById('modal-seuil').classList.add('flex');
    }

    function fermerModalSeuil() {
        formulaireASeuiler = null;
        document.getElementById('modal-seuil').classList.add('hidden');
        document.getElementById('modal-seuil').classList.remove('flex');
    }

    document.getElementById('modal-seuil-annuler').addEventListener('click', fermerModalSeuil);
    document.getElementById('modal-seuil-confirmer').addEventListener('click', () => {
        if (formulaireASeuiler) {
            formulaireASeuiler.submit();
        }
    });

    // Recherche et filtre par statut, appliqués aux lignes de stock
    const rechercheStockInput = document.getElementById('recherche-stock');
    const filtreStatutSelect = document.getElementById('filtre-statut-stock');

    function appliquerFiltreStock() {
        const motCle = rechercheStockInput.value.trim().toLowerCase();
        const statut = filtreStatutSelect.value;

        document.querySelectorAll('[data-ligne-stock]').forEach((ligne) => {
            const correspondNom = ligne.dataset.nomProduit.includes(motCle);
            const correspondStatut = statut === '' || ligne.dataset.statutStock === statut;
            ligne.classList.toggle('hidden', !(correspondNom && correspondStatut));
        });

        document.querySelectorAll('[data-section-stock]').forEach((section) => {
            const aUneLigneVisible = Array.from(section.querySelectorAll('[data-ligne-stock]'))
                .some((ligne) => !ligne.classList.contains('hidden'));
            section.classList.toggle('hidden', !aUneLigneVisible);
        });
    }

    rechercheStockInput.addEventListener('input', appliquerFiltreStock);
    filtreStatutSelect.addEventListener('change', appliquerFiltreStock);
</script>