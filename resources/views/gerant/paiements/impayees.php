<?php

use Core\View;
use DateTimeInterface;

$titrePage = 'Tous les paiements';
$section = 'paiements';

$nombreCommandes = count($commandes);

$totalCommandes = 0;
$totalPaye = 0;
$totalRestant = 0;

foreach ($commandes as $ligne) {
    $totalCommandes += $ligne['montantTotal'];
    $totalPaye += $ligne['montantPaye'];
    $totalRestant += $ligne['montantRestant'];
}

?>

<div class="flex flex-col gap-6">

    <div>
        <h1 class="font-voice text-2xl font-medium text-charbon">
            Tous les paiements
        </h1>

        <p class="mt-1 text-sm text-gris-chaud">
            Consultez et gérez les paiements de toutes les commandes.
        </p>
    </div>

    <!-- Statistiques -->
    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">

        <div class="rounded-lg border border-creme bg-white p-5">
            <p class="text-sm text-gris-chaud">
                Total des commandes
            </p>

            <p class="mt-2 font-voice text-2xl font-medium text-charbon">
                <?= number_format($totalCommandes, 0, ',', ' ') ?> FCFA
            </p>
        </div>

        <div class="rounded-lg border border-creme bg-white p-5">
            <p class="text-sm text-gris-chaud">
                Total encaissé
            </p>

            <p class="mt-2 font-voice text-2xl font-medium text-charbon">
                <?= number_format($totalPaye, 0, ',', ' ') ?> FCFA
            </p>
        </div>

        <div class="rounded-lg border border-creme bg-white p-5">
            <p class="text-sm text-gris-chaud">
                Reste à encaisser
            </p>

            <p class="mt-2 font-voice text-2xl font-medium text-bordeaux">
                <?= number_format($totalRestant, 0, ',', ' ') ?> FCFA
            </p>
        </div>

    </div>

    <!-- Recherche et filtres -->
    <div class="rounded-lg border border-creme bg-white p-5">

        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

            <div class="relative w-full lg:max-w-md">

                <svg
                    class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gris-chaud"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.7"
                >
                    <circle cx="11" cy="11" r="7"/>
                    <path d="m20 20-4-4" stroke-linecap="round"/>
                </svg>

                <input
                    type="text"
                    id="recherche-paiement"
                    placeholder="Rechercher une commande..."
                    class="h-11 w-full rounded-md border border-creme bg-white pl-10 pr-3.5 text-sm text-charbon outline-none transition focus:border-bordeaux focus:ring-3 focus:ring-bordeaux/10"
                >

            </div>

            <div class="flex flex-wrap gap-2">

                <button
                    type="button"
                    data-filtre="TOUS"
                    class="bouton-filtre arrondi-filtre bg-bordeaux px-4 py-2.5 text-sm font-medium text-ivoire"
                >
                    Toutes
                </button>

                <button
                    type="button"
                    data-filtre="IMPAYEE"
                    class="bouton-filtre arrondi-filtre border border-creme bg-white px-4 py-2.5 text-sm font-medium text-charbon transition hover:bg-ivoire"
                >
                    Impayées
                </button>

                <button
                    type="button"
                    data-filtre="PARTIELLE"
                    class="bouton-filtre arrondi-filtre border border-creme bg-white px-4 py-2.5 text-sm font-medium text-charbon transition hover:bg-ivoire"
                >
                    Partiellement payées
                </button>

                <button
                    type="button"
                    data-filtre="PAYEE"
                    class="bouton-filtre arrondi-filtre border border-creme bg-white px-4 py-2.5 text-sm font-medium text-charbon transition hover:bg-ivoire"
                >
                    Payées
                </button>

            </div>

        </div>

    </div>

    <!-- Liste -->
    <div class="overflow-hidden rounded-lg border border-creme bg-white">

        <div class="overflow-x-auto">

            <table class="w-full min-w-[850px] border-collapse">

                <thead>
                    <tr class="border-b border-creme bg-ivoire text-left">
                        <th class="px-5 py-4 text-xs font-medium uppercase tracking-wide text-gris-chaud">Commande</th>
                        <th class="px-5 py-4 text-xs font-medium uppercase tracking-wide text-gris-chaud">Date</th>
                        <th class="px-5 py-4 text-xs font-medium uppercase tracking-wide text-gris-chaud">Total</th>
                        <th class="px-5 py-4 text-xs font-medium uppercase tracking-wide text-gris-chaud">Payé</th>
                        <th class="px-5 py-4 text-xs font-medium uppercase tracking-wide text-gris-chaud">Reste</th>
                        <th class="px-5 py-4 text-xs font-medium uppercase tracking-wide text-gris-chaud">Statut</th>
                        <th class="px-5 py-4 text-right text-xs font-medium uppercase tracking-wide text-gris-chaud">Action</th>
                    </tr>
                </thead>

                <tbody id="liste-paiements">

                    <?php foreach ($commandes as $ligne): ?>

                        <?php
                        $commande = $ligne['commande'];
                        $statutPaiement = $ligne['statutPaiement'];

                        $classeStatut = match ($statutPaiement) {
                            'PAYEE' => 'bg-green-50 text-green-700',
                            'PARTIELLE' => 'bg-orange-50 text-orange-700',
                            default => 'bg-red-50 text-red-700',
                        };

                        $libelleStatut = match ($statutPaiement) {
                            'PAYEE' => 'Payée',
                            'PARTIELLE' => 'Partiellement payée',
                            default => 'Impayée',
                        };
                        ?>

                        <tr
                            class="ligne-paiement border-b border-creme last:border-b-0"
                            data-statut="<?= View::e($statutPaiement) ?>"
                            data-recherche="<?= View::e(strtolower($commande->getNumeroCommande() . ' ' . $statutPaiement)) ?>"
                        >
                            <td class="px-5 py-4">
                                <span class="font-medium text-charbon"><?= View::e($commande->getNumeroCommande()) ?></span>
                            </td>

                            <td class="px-5 py-4 text-sm text-gris-chaud">
                                <?php
                                $dateCommande = $commande->getDateCommande();
                                echo $dateCommande instanceof DateTimeInterface
                                    ? $dateCommande->format('d/m/Y à H:i')
                                    : View::e((string) $dateCommande);
                                ?>
                            </td>

                            <td class="px-5 py-4 text-sm font-medium text-charbon">
                                <?= number_format($ligne['montantTotal'], 0, ',', ' ') ?> FCFA
                            </td>

                            <td class="px-5 py-4 text-sm text-charbon">
                                <?= number_format($ligne['montantPaye'], 0, ',', ' ') ?> FCFA
                            </td>

                            <td class="px-5 py-4 text-sm font-medium <?= $ligne['montantRestant'] > 0 ? 'text-bordeaux' : 'text-charbon' ?>">
                                <?= number_format($ligne['montantRestant'], 0, ',', ' ') ?> FCFA
                            </td>

                            <td class="px-5 py-4">
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-medium <?= $classeStatut ?>">
                                    <?= $libelleStatut ?>
                                </span>
                            </td>

                            <td class="px-5 py-4 text-right">
                                <a href="/gerant/paiements/<?= $commande->getId() ?>"
                                    class="inline-flex items-center rounded-md border border-creme px-3.5 py-2 text-sm font-medium text-charbon transition-colors hover:bg-ivoire">
                                    Voir
                                </a>
                            </td>
                        </tr>

                    <?php endforeach; ?>

                    <tr id="aucun-resultat" class="<?= $nombreCommandes > 0 ? 'hidden' : '' ?>">
                        <td colspan="7" class="px-5 py-12 text-center text-sm text-gris-chaud">
                            Aucune commande trouvée.
                        </td>
                    </tr>

                </tbody>

            </table>

        </div>

    </div>

</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const recherche = document.getElementById('recherche-paiement');
        const boutons = document.querySelectorAll('[data-filtre]');
        const lignes = document.querySelectorAll('.ligne-paiement');
        const aucunResultat = document.getElementById('aucun-resultat');

        let filtreActuel = 'TOUS';

        function filtrerPaiements() {
            const terme = recherche.value.trim().toLowerCase();
            let nombreResultats = 0;

            lignes.forEach((ligne) => {
                const statut = ligne.dataset.statut;
                const contenuRecherche = ligne.dataset.recherche;

                const correspondRecherche = terme === '' || contenuRecherche.includes(terme);
                const correspondFiltre = filtreActuel === 'TOUS' || statut === filtreActuel;
                const afficher = correspondRecherche && correspondFiltre;

                ligne.classList.toggle('hidden', !afficher);

                if (afficher) {
                    nombreResultats++;
                }
            });

            aucunResultat.classList.toggle('hidden', nombreResultats !== 0);
        }

        recherche.addEventListener('input', filtrerPaiements);

        boutons.forEach((bouton) => {
            bouton.addEventListener('click', () => {
                filtreActuel = bouton.dataset.filtre;

                boutons.forEach((element) => {
                    element.classList.remove('bg-bordeaux', 'text-ivoire');
                    element.classList.add('border', 'border-creme', 'bg-white', 'text-charbon');
                });

                bouton.classList.remove('border', 'border-creme', 'bg-white', 'text-charbon');
                bouton.classList.add('bg-bordeaux', 'text-ivoire');

                filtrerPaiements();
            });
        });

        filtrerPaiements();
    });
</script>