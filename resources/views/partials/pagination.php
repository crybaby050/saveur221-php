<?php

use Core\View;

/**
 * @var \Core\Paginateur $pagination
 * @var string           $urlBase    Chemin de la page (ex: '/gerant/categories')
 * @var array            $parametres Paramètres de requête à conserver (ex: ['recherche' => 'riz']), hors 'page'
 */

if ($pagination->aPlusieursPages()):
    $construireUrl = static fn(int $page): string => $urlBase . '?' . http_build_query([...$parametres, 'page' => $page]);
?>
<div class="flex items-center justify-between border-t border-creme px-1 pt-4 text-sm">
    <p class="text-gris-chaud">
        Page <?= $pagination->page ?> sur <?= $pagination->totalPages ?> (<?= $pagination->total ?> résultats)
    </p>
    <div class="flex items-center gap-1">
        
        <a href="<?= View::e($construireUrl(max(1, $pagination->page - 1))) ?>"
            class="rounded-md border border-creme px-3 py-1.5 text-xs font-medium text-charbon transition-colors hover:bg-ivoire <?= $pagination->page <= 1 ? 'pointer-events-none opacity-40' : '' ?>"
        >Précédent</a>
        
        <a href="<?= View::e($construireUrl(min($pagination->totalPages, $pagination->page + 1))) ?>"
            class="rounded-md border border-creme px-3 py-1.5 text-xs font-medium text-charbon transition-colors hover:bg-ivoire <?= $pagination->page >= $pagination->totalPages ? 'pointer-events-none opacity-40' : '' ?>"
        >Suivant</a>
    </div>
</div>
<?php endif; ?>