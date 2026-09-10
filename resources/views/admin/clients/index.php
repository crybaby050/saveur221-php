<?php

use Core\View;

$titrePage = 'Clients';
$section = 'clients';
?>

<div class="space-y-6">

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <form method="get" action="/admin/clients" class="relative w-full sm:max-w-xs">
            <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gris-chaud" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                <circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3" stroke-linecap="round"/>
            </svg>
            <input
                type="text"
                name="recherche"
                value="<?= View::e($motCle ?? '') ?>"
                placeholder="Rechercher un client..."
                class="h-10 w-full rounded-md border border-creme bg-white pl-9 pr-3 text-sm text-charbon focus:border-bordeaux focus:outline-none focus:ring-3 focus:ring-bordeaux/10"
            >
        </form>

        <?php include __DIR__ . '/../../partials/bascule-affichage.php'; ?>
    </div>

    <?php if (empty($clients)): ?>
        <div class="rounded-xl border border-dashed border-creme bg-white p-10 text-center text-sm text-gris-chaud">
            Aucun client trouvé.
        </div>
    <?php else: ?>

        <!-- Vue grille -->
        <div data-vue-contenu="grille" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            <?php foreach ($clients as $client): ?>
                <?php $initiales = mb_strtoupper(mb_substr($client->getPrenom(), 0, 1) . mb_substr($client->getNom(), 0, 1)); ?>
                <a href="/admin/clients/<?= $client->getId() ?>" class="block rounded-xl border border-creme bg-white p-5 shadow-sm transition-shadow hover:shadow-md">
                    <div class="flex items-center gap-3">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-bordeaux text-sm font-medium text-ivoire">
                            <?= View::e($initiales) ?>
                        </div>
                        <div class="min-w-0">
                            <p class="truncate font-medium text-charbon"><?= View::e($client->getPrenom() . ' ' . $client->getNom()) ?></p>
                            <p class="truncate text-xs text-gris-chaud"><?= View::e($client->getEmail()) ?></p>
                        </div>
                    </div>

                    <div class="mt-3 space-y-1 text-xs text-gris-chaud">
                        <?php if ($client->getTelephone()): ?>
                            <p><?= View::e($client->getTelephone()) ?></p>
                        <?php endif; ?>
                        <?php if ($client->getAdresse()): ?>
                            <p class="truncate"><?= View::e($client->getAdresse()) ?></p>
                        <?php endif; ?>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Vue tableau -->
        <div data-vue-contenu="table" class="hidden overflow-x-auto rounded-xl border border-creme bg-white shadow-sm">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-creme text-xs uppercase tracking-wide text-gris-chaud">
                        <th class="px-5 py-3 font-medium">Client</th>
                        <th class="px-5 py-3 font-medium">Téléphone</th>
                        <th class="px-5 py-3 font-medium">Adresse</th>
                        <th class="px-5 py-3 font-medium text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-creme">
                    <?php foreach ($clients as $client): ?>
                        <?php $initiales = mb_strtoupper(mb_substr($client->getPrenom(), 0, 1) . mb_substr($client->getNom(), 0, 1)); ?>
                        <tr class="hover:bg-ivoire">
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-bordeaux text-xs font-medium text-ivoire">
                                        <?= View::e($initiales) ?>
                                    </div>
                                    <div>
                                        <p class="font-medium text-charbon"><?= View::e($client->getPrenom() . ' ' . $client->getNom()) ?></p>
                                        <p class="text-xs text-gris-chaud"><?= View::e($client->getEmail()) ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3 text-gris-chaud"><?= View::e($client->getTelephone() ?? '—') ?></td>
                            <td class="px-5 py-3 text-gris-chaud"><?= View::e($client->getAdresse() ?? '—') ?></td>
                            <td class="px-5 py-3 text-right">
                                <a href="/admin/clients/<?= $client->getId() ?>" class="rounded-md border border-creme px-3 py-1.5 text-xs font-medium text-charbon transition-colors hover:bg-ivoire">
                                    Voir
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php
        $urlBase = '/admin/clients';
        $parametres = $motCle !== null && $motCle !== '' ? ['recherche' => $motCle] : [];
        include __DIR__ . '/../../partials/pagination.php';
        ?>

    <?php endif; ?>

</div>