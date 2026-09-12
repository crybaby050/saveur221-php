<?php

use Core\View;

$titrePage = 'Détail client';
$section = 'clients';

$badgeStatut = static function (string $valeurStatut): array {
    return match ($valeurStatut) {
        'EN_ATTENTE' => ['label' => 'En attente', 'classes' => 'bg-alerte/10 text-alerte'],
        'EN_PREPARATION' => ['label' => 'En préparation', 'classes' => 'bg-info/10 text-info'],
        'PRETE' => ['label' => 'Prête', 'classes' => 'bg-succes/10 text-succes'],
        'RETIREE' => ['label' => 'Retirée', 'classes' => 'bg-creme text-gris-chaud'],
        'ANNULEE' => ['label' => 'Annulée', 'classes' => 'bg-danger/10 text-danger'],
        default => ['label' => $valeurStatut, 'classes' => 'bg-creme text-gris-chaud'],
    };
};

$initiales = mb_strtoupper(mb_substr($client->getPrenom(), 0, 1) . mb_substr($client->getNom(), 0, 1));
$totalCommandes = count($commandes);
$totalDepense = array_reduce($commandes, fn(float $somme, $commande) => $somme + $commande->getMontantTotal(), 0.0);
?>

<div class="space-y-6">

    <a href="/admin/clients" class="inline-flex items-center gap-1.5 text-sm text-gris-chaud transition-colors hover:text-charbon">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
            <path d="M15 18l-6-6 6-6" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        Retour aux clients
    </a>

    <!-- Fiche client -->
    <div class="rounded-xl border border-creme bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-center gap-4">
            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-bordeaux text-lg font-medium text-ivoire">
                <?= View::e($initiales) ?>
            </div>
            <div>
                <p class="font-voice text-lg font-medium text-charbon"><?= View::e($client->getPrenom() . ' ' . $client->getNom()) ?></p>
                <p class="text-sm text-gris-chaud"><?= View::e($client->getEmail()) ?></p>
            </div>
        </div>

        <div class="mt-5 grid grid-cols-1 gap-4 border-t border-creme pt-5 sm:grid-cols-2">
            <div>
                <p class="text-xs uppercase tracking-wide text-gris-chaud">Téléphone</p>
                <p class="mt-1 text-sm text-charbon"><?= View::e($client->getTelephone() ?? 'Non renseigné') ?></p>
            </div>
            <div>
                <p class="text-xs uppercase tracking-wide text-gris-chaud">Adresse</p>
                <p class="mt-1 text-sm text-charbon"><?= View::e($client->getAdresse() ?? 'Non renseignée') ?></p>
            </div>
        </div>
    </div>

    <!-- Résumé activité -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div class="rounded-xl border border-creme bg-white p-5 shadow-sm">
            <p class="text-xs uppercase tracking-wide text-gris-chaud">Commandes passées</p>
            <p class="mt-1 font-voice text-2xl font-medium text-charbon"><?= $totalCommandes ?></p>
        </div>
        <div class="rounded-xl border border-creme bg-white p-5 shadow-sm">
            <p class="text-xs uppercase tracking-wide text-gris-chaud">Total dépensé</p>
            <p class="mt-1 font-voice text-2xl font-medium text-charbon">
                <?= number_format($totalDepense, 0, ',', ' ') ?> FCFA
            </p>
        </div>
    </div>

    <!-- Historique des commandes -->
    <div class="rounded-xl border border-creme bg-white shadow-sm">
        <div class="border-b border-creme px-5 py-4">
            <p class="text-sm font-medium text-charbon">Historique des commandes</p>
        </div>

        <?php if (empty($commandes)): ?>
            <p class="px-5 py-6 text-sm text-gris-chaud">Ce client n'a passé aucune commande.</p>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[550px] text-left text-sm">
                    <thead>
                        <tr class="border-b border-creme text-xs uppercase tracking-wide text-gris-chaud">
                            <th class="px-5 py-3 font-medium">Numéro</th>
                            <th class="px-5 py-3 font-medium">Date</th>
                            <th class="px-5 py-3 font-medium">Montant</th>
                            <th class="px-5 py-3 font-medium">Statut</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-creme">
                        <?php foreach ($commandes as $commande): ?>
                            <?php $badge = $badgeStatut($commande->getStatut()->value); ?>
                            <tr class="hover:bg-ivoire">
                                <td class="px-5 py-3 font-medium text-charbon"><?= View::e($commande->getNumeroCommande()) ?></td>
                                <td class="px-5 py-3 text-gris-chaud"><?= View::e($commande->getDateCommande()->format('d/m/Y H:i')) ?></td>
                                <td class="px-5 py-3 text-charbon"><?= number_format($commande->getMontantTotal(), 0, ',', ' ') ?> FCFA</td>
                                <td class="px-5 py-3">
                                    <span class="rounded-full px-2.5 py-1 text-xs font-medium <?= $badge['classes'] ?>">
                                        <?= View::e($badge['label']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

</div>