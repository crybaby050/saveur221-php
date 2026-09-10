<?php

use Core\View;

$titrePage = 'Avis clients';
$section = 'avis';

function etoiles_avis(int $note): string
{
    $html = '';
    for ($i = 1; $i <= 5; $i++) {
        $html .= $i <= $note
            ? '<span class="text-or">★</span>'
            : '<span class="text-creme">★</span>';
    }
    return $html;
}
?>

<div class="space-y-6">

    <!-- Filtre par note -->
    <div class="flex flex-wrap items-center gap-2">
        <a href="/admin/avis" class="rounded-full border px-3 py-1.5 text-xs font-medium transition-colors <?= $noteActive === null ? 'border-bordeaux bg-bordeaux text-ivoire' : 'border-creme text-charbon hover:bg-ivoire' ?>">
            Toutes
        </a>
        <?php for ($n = 5; $n >= 1; $n--): ?>
            <a href="/admin/avis?note=<?= $n ?>" class="rounded-full border px-3 py-1.5 text-xs font-medium transition-colors <?= $noteActive === $n ? 'border-bordeaux bg-bordeaux text-ivoire' : 'border-creme text-charbon hover:bg-ivoire' ?>">
                <?= $n ?> ★
            </a>
        <?php endfor; ?>
    </div>

    <?php if (empty($avisEnrichis)): ?>
        <div class="rounded-xl border border-dashed border-creme bg-white p-10 text-center text-sm text-gris-chaud">
            Aucun avis trouvé.
        </div>
    <?php else: ?>

        <div class="space-y-3">
            <?php foreach ($avisEnrichis as $entree): ?>
                <?php $avis = $entree['avis']; ?>
                <div class="rounded-xl border border-creme bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-medium text-charbon"><?= View::e($entree['produitNom']) ?></p>
                            <p class="mt-0.5 text-xs text-gris-chaud">
                                Par <?= View::e($entree['clientNom']) ?> · <?= View::e($avis->getDateAvis()->format('d/m/Y')) ?>
                            </p>
                            <p class="mt-1.5 text-sm"><?= etoiles_avis($avis->getNote()) ?></p>
                        </div>

                        <form method="post" action="/admin/avis/<?= $avis->getId() ?>/supprimer">
                            <button type="button"
                                data-confirm-titre="Supprimer l'avis"
                                data-confirm-message="Cette action est irréversible."
                                data-confirm-label="Supprimer"
                                data-confirm-class="bg-danger hover:bg-danger/90"
                                class="shrink-0 rounded-md border border-danger/30 px-3 py-1.5 text-xs font-medium text-danger transition-colors hover:bg-danger/10">
                                Supprimer
                            </button>
                        </form>
                    </div>

                    <?php if ($avis->getCommentaire()): ?>
                        <p class="mt-3 border-t border-creme pt-3 text-sm text-charbon"><?= View::e($avis->getCommentaire()) ?></p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <?php
        $urlBase = '/admin/avis';
        $parametres = $noteActive !== null ? ['note' => $noteActive] : [];
        include __DIR__ . '/../../partials/pagination.php';
        ?>

    <?php endif; ?>

</div>