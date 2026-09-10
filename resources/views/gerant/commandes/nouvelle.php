<?php

use Core\View;

$titrePage = 'Nouvelle commande';
$section = 'commandes';
?>

<div class="space-y-6">

    <a href="/gerant/commandes" class="inline-flex items-center gap-1.5 text-sm text-gris-chaud transition-colors hover:text-charbon">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
            <path d="M15 18l-6-6 6-6" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        Retour aux commandes
    </a>

    <?php if (isset($erreur)): ?>
        <p class="rounded-md border-l-4 border-danger bg-danger/10 px-3.5 py-2.5 text-sm text-danger">
            <?= View::e($erreur) ?>
        </p>
    <?php endif; ?>

    <!-- Étape 1 : recherche du client par téléphone -->
    <div class="rounded-xl border border-creme bg-white p-5 shadow-sm">
        <p class="text-sm font-medium text-charbon">1. Rechercher le client par téléphone</p>
        <form method="get" action="/gerant/commandes/nouvelle" class="mt-3 flex flex-col gap-2 sm:flex-row">
            <input
                type="text"
                name="telephone"
                value="<?= View::e($telephone ?? '') ?>"
                placeholder="Numéro de téléphone..."
                class="h-10 flex-1 rounded-md border border-creme bg-white px-3 text-sm text-charbon focus:border-bordeaux focus:outline-none focus:ring-3 focus:ring-bordeaux/10"
            >
            <button type="submit" class="h-10 rounded-md bg-bordeaux px-4 text-sm font-medium text-ivoire transition-colors hover:bg-bordeaux-sombre">
                Rechercher
            </button>
        </form>

        <?php if ($telephone !== null && $telephone !== ''): ?>
            <?php if (empty($clientsTrouves)): ?>
                <p class="mt-3 text-sm text-gris-chaud">Aucun client trouvé avec ce numéro.</p>
            <?php else: ?>
                <div class="mt-3 divide-y divide-creme border-t border-creme">
                    <?php foreach ($clientsTrouves as $clientTrouve): ?>
                        <a href="/gerant/commandes/nouvelle?client_id=<?= $clientTrouve->getId() ?>"
                            class="flex items-center justify-between px-2 py-3 text-sm transition-colors hover:bg-ivoire <?= ($clientSelectionne?->getId() === $clientTrouve->getId()) ? 'bg-ivoire' : '' ?>">
                            <span class="font-medium text-charbon"><?= View::e(trim($clientTrouve->getPrenom() . ' ' . $clientTrouve->getNom())) ?></span>
                            <span class="text-gris-chaud"><?= View::e($clientTrouve->getTelephone() ?? '—') ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <!-- Étape 2 : sélection des produits, uniquement si un client est choisi -->
    <?php if ($clientSelectionne !== null): ?>
        <div class="rounded-xl border border-creme bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-charbon">
                2. Commande pour
                <span class="text-bordeaux"><?= View::e(trim($clientSelectionne->getPrenom() . ' ' . $clientSelectionne->getNom())) ?></span>
            </p>

            <form method="post" action="/gerant/commandes/creer" class="mt-4 space-y-4">
                <input type="hidden" name="client_id" value="<?= $clientSelectionne->getId() ?>">

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <input
                        type="text"
                        id="recherche-produit-commande"
                        placeholder="Rechercher un produit..."
                        class="h-10 w-full rounded-md border border-creme bg-white px-3 text-sm text-charbon focus:border-bordeaux focus:outline-none focus:ring-3 focus:ring-bordeaux/10 sm:max-w-xs"
                    >
                    <div>
                        <label for="statut-initial" class="mr-2 text-sm text-charbon">Statut initial</label>
                        <select id="statut-initial" name="statut_initial" required
                            class="h-10 rounded-md border border-creme bg-white px-3 text-sm text-charbon focus:border-bordeaux focus:outline-none focus:ring-3 focus:ring-bordeaux/10">
                            <option value="PRETE">Prête</option>
                            <option value="RETIREE">Retirée (déjà remise au client)</option>
                        </select>
                    </div>
                </div>

                <div class="max-h-96 overflow-y-auto rounded-md border border-creme">
                    <div class="divide-y divide-creme">
                        <?php foreach ($produits as $produit): ?>
                            <div
                                data-ligne-produit-commande
                                data-nom-produit="<?= View::e(mb_strtolower($produit->getLibelle())) ?>"
                                class="flex items-center justify-between gap-3 px-4 py-2.5"
                            >
                                <div class="flex items-center gap-3">
                                    <img src="<?= View::e($produit->getImage() ?? '/assets/images/produit-defaut.svg') ?>" alt="" class="h-9 w-9 shrink-0 rounded-md object-cover">
                                    <div>
                                        <p class="text-sm font-medium text-charbon"><?= View::e($produit->getLibelle()) ?></p>
                                        <p class="text-xs text-gris-chaud">
                                            <?= number_format($produit->getPrix(), 0, ',', ' ') ?> FCFA · <?= $produit->getQuantiteStock() ?> en stock
                                        </p>
                                    </div>
                                </div>
                                <input
                                    type="number"
                                    name="quantite[<?= $produit->getId() ?>]"
                                    min="0"
                                    max="<?= $produit->getQuantiteStock() ?>"
                                    value="0"
                                    class="h-9 w-20 rounded-md border border-creme bg-white px-2.5 text-sm text-charbon focus:border-bordeaux focus:outline-none focus:ring-3 focus:ring-bordeaux/10"
                                >
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="rounded-md bg-bordeaux px-5 py-2.5 text-sm font-medium text-ivoire transition-colors hover:bg-bordeaux-sombre">
                        Créer la commande
                    </button>
                </div>
            </form>
        </div>

        <script>
            const rechercheProduitCommande = document.getElementById('recherche-produit-commande');
            rechercheProduitCommande.addEventListener('input', () => {
                const motCle = rechercheProduitCommande.value.trim().toLowerCase();
                document.querySelectorAll('[data-ligne-produit-commande]').forEach((ligne) => {
                    ligne.classList.toggle('hidden', !ligne.dataset.nomProduit.includes(motCle));
                });
            });
        </script>
    <?php endif; ?>

</div>