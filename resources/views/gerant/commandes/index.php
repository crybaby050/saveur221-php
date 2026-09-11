<?php

use Core\View;

$titrePage = 'Commandes';
$section = 'commandes';

$statutActif = $_GET['statut'] ?? '';

$statutsDisponibles = [
    '' => 'Tous les statuts',
    'EN_ATTENTE' => 'En attente',
    'EN_PREPARATION' => 'En préparation',
    'PRETE' => 'Prête',
    'RETIREE' => 'Retirée',
    'ANNULEE' => 'Annulée',
];

function badge_statut_commande(string $statut): string
{
    return match ($statut) {
        'EN_ATTENTE' => 'bg-or/10 text-or-clair border-or/30',
        'EN_PREPARATION' => 'bg-bordeaux/10 text-bordeaux border-bordeaux/30',
        'PRETE' => 'bg-succes/10 text-succes border-succes/30',
        'RETIREE' => 'bg-gris-chaud/10 text-gris-chaud border-gris-chaud/30',
        'ANNULEE' => 'bg-danger/10 text-danger border-danger/30',
        default => 'bg-creme text-charbon border-creme',
    };
}
?>

<div class="space-y-6">

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <form method="get" action="/gerant/commandes/rechercher" class="flex flex-1 items-center gap-2 sm:max-w-xs">
            <input
                type="text"
                name="numero"
                placeholder="Rechercher par numéro (CMD-...)"
                class="h-10 w-full rounded-md border border-creme bg-white px-3 text-sm text-charbon focus:border-bordeaux focus:outline-none focus:ring-3 focus:ring-bordeaux/10"
            >
            <button type="submit" class="h-10 shrink-0 rounded-md border border-creme px-3 text-sm font-medium text-charbon transition-colors hover:bg-ivoire">
                Chercher
            </button>
        </form>

        <div class="flex items-center gap-3">
            <form method="get" action="/gerant/commandes">
                <select
                    name="statut"
                    onchange="this.form.submit()"
                    class="h-10 rounded-md border border-creme bg-white px-3 text-sm text-charbon focus:border-bordeaux focus:outline-none focus:ring-3 focus:ring-bordeaux/10"
                >
                    <?php foreach ($statutsDisponibles as $valeur => $libelle): ?>
                        <option value="<?= View::e($valeur) ?>" <?= $statutActif === $valeur ? 'selected' : '' ?>>
                            <?= View::e($libelle) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>

            <a href="/gerant/commandes/nouvelle"
                class="inline-flex items-center justify-center gap-2 rounded-md bg-bordeaux px-4 py-2.5 text-sm font-medium text-ivoire transition-colors hover:bg-bordeaux-sombre">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                    <path d="M12 5v14M5 12h14" stroke-linecap="round"/>
                </svg>
                Nouvelle commande
            </a>
        </div>
    </div>

    <?php if (isset($erreur)): ?>
        <p class="rounded-md border-l-4 border-danger bg-danger/10 px-3.5 py-2.5 text-sm text-danger">
            <?= View::e($erreur) ?>
        </p>
    <?php endif; ?>

    <?php if (empty($commandes)): ?>
        <div class="rounded-xl border border-dashed border-creme bg-white p-10 text-center text-sm text-gris-chaud">
            Aucune commande trouvée.
        </div>
    <?php else: ?>
        <div class="overflow-x-auto rounded-xl border border-creme bg-white shadow-sm">
            <table class="w-full min-w-[700px] text-left text-sm">
                <thead>
                    <tr class="border-b border-creme text-xs uppercase tracking-wide text-gris-chaud">
                        <th class="px-5 py-3 font-medium">Numéro</th>
                        <th class="px-5 py-3 font-medium">Date</th>
                        <th class="px-5 py-3 font-medium">Montant</th>
                        <th class="px-5 py-3 font-medium">Statut</th>
                        <th class="px-5 py-3 font-medium">Paiement</th>
                        <th class="px-5 py-3 font-medium text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-creme">
                    <?php foreach ($commandes as $commande): ?>
                        <tr class="hover:bg-ivoire">
                            <td class="px-5 py-3 font-medium text-charbon"><?= View::e($commande->getNumeroCommande()) ?></td>
                            <td class="px-5 py-3 text-gris-chaud"><?= $commande->getDateCommande()->format('d/m/Y H:i') ?></td>
                            <td class="px-5 py-3 text-charbon"><?= number_format($commande->getMontantTotal(), 0, ',', ' ') ?> FCFA</td>
                            <td class="px-5 py-3">
                                <span class="rounded-full border px-2 py-0.5 text-xs font-medium <?= badge_statut_commande($commande->getStatut()->value) ?>">
                                    <?= View::e($statutsDisponibles[$commande->getStatut()->value] ?? $commande->getStatut()->value) ?>
                                </span>
                            </td>
                            <td class="px-5 py-3 text-gris-chaud"><?= View::e($commande->getStatutPaiement()->value) ?></td>
                            <td class="px-5 py-3 text-right">
                                <a href="/gerant/commandes/<?= $commande->getId() ?>"
                                    class="rounded-md border border-creme px-3 py-1.5 text-xs font-medium text-charbon transition-colors hover:bg-ivoire">
                                    Détail
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

</div>