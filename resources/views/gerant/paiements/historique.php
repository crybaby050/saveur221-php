<?php

use Core\View;

$titrePage = 'Paiements de la commande';
$section = 'paiements';

$montantDejaPaye = array_reduce(
    $paiements,
    fn(float $total, $p) => $total + $p->getMontant(),
    0.0
);

$montantTotal = $commande->getMontantTotal();
$montantRestant = max(0, $montantTotal - $montantDejaPaye);

?>

<!-- Drawer enregistrement paiement -->
<div id="tiroir-paiement" class="tiroir-overlay">
    <div class="tiroir-panneau">

        <div class="flex items-center justify-between border-b border-creme px-6 py-4">
            <p class="font-voice text-lg font-medium text-charbon">
                Enregistrer un paiement
            </p>

            <button
                type="button"
                data-fermer-tiroir
                class="text-gris-chaud hover:text-charbon"
            >
                <svg
                    class="h-5 w-5"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.7"
                >
                    <path
                        d="M6 6l12 12M18 6 6 18"
                        stroke-linecap="round"
                    />
                </svg>
            </button>
        </div>

        <div class="px-6 py-5">

            <!-- Informations sur le paiement -->
            <div class="mb-4 rounded-md bg-ivoire px-4 py-3 text-sm">

                <div class="flex items-center justify-between">
                    <span class="text-gris-chaud">
                        Montant total
                    </span>

                    <span class="font-medium text-charbon">
                        <?= number_format($montantTotal, 0, ',', ' ') ?> FCFA
                    </span>
                </div>

                <div class="mt-2 flex items-center justify-between">
                    <span class="text-gris-chaud">
                        Déjà réglé
                    </span>

                    <span class="font-medium text-charbon">
                        <?= number_format($montantDejaPaye, 0, ',', ' ') ?> FCFA
                    </span>
                </div>

                <div class="mt-3 flex items-center justify-between border-t border-creme pt-3">
                    <span class="font-medium text-charbon">
                        Reste à payer
                    </span>

                    <span class="font-semibold text-bordeaux">
                        <?= number_format($montantRestant, 0, ',', ' ') ?> FCFA
                    </span>
                </div>

            </div>

            <!-- Message d'erreur -->
            <?php if (isset($erreur)): ?>
                <p class="mb-4 rounded-md border-l-4 border-danger bg-danger/10 px-3.5 py-2.5 text-sm text-danger">
                    <?= View::e($erreur) ?>
                </p>
            <?php endif; ?>

            <!-- Formulaire -->
            <form
                method="post"
                action="/gerant/paiements/<?= $commandeId ?>"
                class="flex flex-col gap-4"
            >
                <div>

                    <label
                        for="montant"
                        class="mb-1.5 block text-sm text-charbon"
                    >
                        Montant à encaisser (FCFA)
                    </label>

                    <input
                        type="number"
                        id="montant"
                        name="montant"
                        step="1"
                        value="<?= isset($_POST['montant']) ? View::e($_POST['montant']) : '' ?>"
                        class="h-11 w-full rounded-md border border-creme bg-white px-3.5 text-sm text-charbon focus:border-bordeaux focus:outline-none focus:ring-3 focus:ring-bordeaux/10"
                    >

                    <p class="mt-1.5 text-xs text-gris-chaud">
                        Maximum autorisé :
                        <?= number_format($montantRestant, 0, ',', ' ') ?> FCFA
                    </p>

                </div>

                <div class="mt-2 flex justify-end gap-3">

                    <button
                        type="button"
                        data-fermer-tiroir
                        class="rounded-md border border-creme px-4 py-2.5 text-sm font-medium text-charbon transition-colors hover:bg-ivoire"
                    >
                        Annuler
                    </button>

                    <button
                        type="submit"
                        class="rounded-md bg-bordeaux px-5 py-2.5 text-sm font-medium text-ivoire transition-colors hover:bg-bordeaux-sombre"
                    >
                        Enregistrer
                    </button>

                </div>
            </form>

        </div>
    </div>
</div>

<div class="space-y-6">

    <!-- En-tête -->
    <div class="flex items-center justify-between">

        <a
            href="/gerant/paiements/impayees"
            class="inline-flex items-center gap-1.5 text-sm text-gris-chaud transition-colors hover:text-charbon"
        >
            <svg
                class="h-4 w-4"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.7"
            >
                <path
                    d="M15 18l-6-6 6-6"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                />
            </svg>

            Retour
        </a>

        <button
            type="button"
            onclick="ouvrirTiroir('tiroir-paiement')"
            class="inline-flex items-center justify-center gap-2 rounded-md bg-bordeaux px-4 py-2.5 text-sm font-medium text-ivoire transition-colors hover:bg-bordeaux-sombre"
        >
            <svg
                class="h-4 w-4"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.7"
            >
                <path
                    d="M12 5v14M5 12h14"
                    stroke-linecap="round"
                />
            </svg>

            Enregistrer un paiement
        </button>

    </div>

    <!-- Paiements -->
    <div class="rounded-xl border border-creme bg-white shadow-sm">

        <div class="border-b border-creme px-5 py-3">
            <p class="text-sm font-medium text-charbon">
                Historique des paiements
            </p>
        </div>

        <?php if (empty($paiements)): ?>

            <p class="px-5 py-6 text-sm text-gris-chaud">
                Aucun paiement enregistré pour cette commande.
            </p>

        <?php else: ?>

            <div class="divide-y divide-creme">

                <?php foreach ($paiements as $paiement): ?>

                    <div class="flex items-center justify-between px-5 py-3.5 text-sm">

                        <span class="text-charbon">
                            <?= $paiement->getDatePaiement()->format('d/m/Y à H:i') ?>
                        </span>

                        <span class="font-medium text-charbon">
                            <?= number_format($paiement->getMontant(), 0, ',', ' ') ?> FCFA
                        </span>

                    </div>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </div>

    <!-- Reçus -->
    <div class="rounded-xl border border-creme bg-white shadow-sm">

        <div class="border-b border-creme px-5 py-3">
            <p class="text-sm font-medium text-charbon">
                Reçus émis
            </p>
        </div>

        <?php if (empty($recus)): ?>

            <p class="px-5 py-6 text-sm text-gris-chaud">
                Aucun reçu émis pour cette commande.
            </p>

        <?php else: ?>

            <div class="divide-y divide-creme">

                <?php foreach ($recus as $recu): ?>

                    <div class="flex items-center justify-between px-5 py-3.5 text-sm">

                        <div>

                            <p class="font-medium text-charbon">
                                <?= View::e($recu->getNumeroRecu()) ?>
                            </p>

                            <p class="text-xs text-gris-chaud">
                                <?= $recu->getDateEmission()->format('d/m/Y à H:i') ?>
                            </p>

                        </div>

                        <span
                            class="rounded-full border px-2.5 py-0.5 text-xs font-medium <?= $recu->getTypePaiement()->value === 'TOTAL'
                                ? 'border-succes/30 bg-succes/10 text-succes'
                                : 'border-or/30 bg-or/10 text-or-clair' ?>"
                        >
                            <?= $recu->getTypePaiement()->value === 'TOTAL'
                                ? 'Solde complet'
                                : 'Partiel' ?>
                        </span>

                    </div>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </div>

</div>
