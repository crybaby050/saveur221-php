<?php

use Core\View;

$erreurAvis = $_GET['erreur'] ?? null;

function etoiles_affichage(float|int|null $note): string
{
    if ($note === null) {
        return '';
    }

    $pleines = (int) round($note);
    $etoiles = '';

    for ($i = 1; $i <= 5; $i++) {
        $etoiles .= $i <= $pleines
            ? '<svg class="h-4 w-4 fill-current text-rouge" viewBox="0 0 24 24"><path d="M12 2.5 14.8 9l7 .6-5.3 4.6 1.6 6.8L12 17.6 5.9 21l1.6-6.8L2.2 9.6l7-.6L12 2.5Z"/></svg>'
            : '<svg class="h-4 w-4 text-creme" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 2.5 14.8 9l7 .6-5.3 4.6 1.6 6.8L12 17.6 5.9 21l1.6-6.8L2.2 9.6l7-.6L12 2.5Z"/></svg>';
    }

    return $etoiles;
}
?>

<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

    <!-- Colonne produit -->
    <div class="lg:col-span-2">

        <a href="/produits" class="inline-flex items-center gap-1.5 text-sm text-gris-chaud transition-colors hover:text-rouge">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                <path d="M15 18l-6-6 6-6" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Retour au menu
        </a>

        <div class="mt-4 grid grid-cols-1 gap-6 sm:grid-cols-2">

            <div class="overflow-hidden rounded-3xl bg-white shadow-sm">
                <img
                    src="<?= View::e($produit->getImage() ?? '/assets/images/produit-defaut.svg') ?>"
                    alt="<?= View::e($produit->getLibelle()) ?>"
                    class="h-72 w-full object-cover sm:h-full"
                >
            </div>

            <div class="flex flex-col rounded-3xl bg-white p-6 shadow-sm">
                <h1 class="font-voice text-2xl font-black text-charbon"><?= View::e($produit->getLibelle()) ?></h1>

                <?php if ($produit->getDescription()): ?>
                    <p class="mt-2 text-sm leading-relaxed text-gris-chaud"><?= View::e($produit->getDescription()) ?></p>
                <?php endif; ?>

                <p class="mt-4 text-2xl font-black text-rouge">
                    <?= number_format($produit->getPrix(), 0, ',', ' ') ?> FCFA
                </p>

                <?php if ($produit->isDisponible()): ?>
                    <form method="post" action="/panier/ajouter" class="mt-6 flex flex-col gap-4">
                        <input type="hidden" name="produit_id" value="<?= $produit->getId() ?>">

                        <div>
                            <p class="mb-2 text-xs font-bold uppercase tracking-wide text-gris-chaud">Quantité</p>
                            <div class="inline-flex items-center gap-4 rounded-full border border-creme px-2 py-1.5">
                                <button type="button" onclick="ajusterQuantiteDetail(-1)" class="flex h-8 w-8 items-center justify-center rounded-full text-lg font-bold text-charbon transition-colors hover:bg-creme">−</button>
                                <span id="quantite-affichee-detail" class="w-4 text-center text-sm font-bold text-charbon">1</span>
                                <button type="button" onclick="ajusterQuantiteDetail(1)" class="flex h-8 w-8 items-center justify-center rounded-full text-lg font-bold text-charbon transition-colors hover:bg-creme">+</button>
                            </div>
                            <input type="hidden" id="quantite-input-detail" name="quantite" value="1">
                        </div>

                        <button type="submit" class="mt-2 flex items-center justify-center gap-2 rounded-full bg-rouge py-3.5 text-sm font-bold text-white transition-colors hover:bg-rouge-clair">
                            Ajouter au panier
                        </button>
                    </form>
                <?php else: ?>
                    <p class="mt-6 rounded-full bg-creme px-4 py-3 text-center text-sm font-medium text-gris-chaud">
                        Ce produit est actuellement indisponible.
                    </p>
                <?php endif; ?>
            </div>

        </div>

        <!-- Suggestions -->
        <?php if (!empty($suggestions)): ?>
            <section class="mt-8">
                <h2 class="text-sm font-black text-charbon">Vous aimerez aussi</h2>
                <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-3">
                    <?php foreach ($suggestions as $suggestion): ?>
                        <a href="/produits/<?= $suggestion->getId() ?>" class="flex items-center gap-3 rounded-2xl bg-white p-3 shadow-sm transition-shadow hover:shadow-md">
                            <img
                                src="<?= View::e($suggestion->getImage() ?? '/assets/images/produit-defaut.svg') ?>"
                                alt=""
                                class="h-14 w-14 shrink-0 rounded-xl object-cover"
                            >
                            <div class="min-w-0">
                                <p class="truncate text-xs font-bold text-charbon"><?= View::e($suggestion->getLibelle()) ?></p>
                                <p class="mt-0.5 text-xs font-black text-rouge"><?= number_format($suggestion->getPrix(), 0, ',', ' ') ?> FCFA</p>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <!-- Avis -->
        <section class="mt-8">
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-black text-charbon">Avis clients</h2>
                <?php if ($noteMoyenne !== null): ?>
                    <div class="flex items-center gap-1.5">
                        <span class="flex items-center gap-0.5"><?= etoiles_affichage($noteMoyenne) ?></span>
                        <span class="text-xs font-bold text-charbon"><?= number_format($noteMoyenne, 1) ?>/5</span>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($erreurAvis): ?>
                <p class="mt-3 rounded-2xl border-l-4 border-rouge bg-rouge/10 px-4 py-3 text-sm text-rouge">
                    <?= View::e($erreurAvis) ?>
                </p>
            <?php endif; ?>

            <!-- Formulaire de dépôt, uniquement si le client est éligible -->
            <?php if ($peutDeposerAvis): ?>
                <div class="mt-4 rounded-2xl bg-white p-5 shadow-sm">
                    <p class="text-sm font-bold text-charbon">Donner votre avis</p>
                    <form method="post" action="/produits/<?= $produit->getId() ?>/avis" class="mt-3">
                        <div class="flex items-center gap-1" id="selecteur-etoiles">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <button type="button" data-valeur-etoile="<?= $i ?>" class="etoile-selection text-2xl text-creme transition-colors hover:text-rouge" aria-label="<?= $i ?> étoile(s)">
                                    ★
                                </button>
                            <?php endfor; ?>
                            <input type="hidden" name="note" id="champ-note" value="0" required>
                        </div>

                        <textarea
                            name="commentaire"
                            rows="3"
                            placeholder="Partagez votre expérience (facultatif)..."
                            class="mt-3 w-full rounded-2xl border border-creme bg-white px-4 py-3 text-sm text-charbon placeholder:text-gris-chaud focus:border-rouge focus:outline-none focus:ring-3 focus:ring-rouge/10"
                        ></textarea>

                        <button type="submit" class="mt-3 rounded-full bg-rouge px-5 py-2.5 text-sm font-bold text-white transition-colors hover:bg-rouge-clair">
                            Publier mon avis
                        </button>
                    </form>
                </div>

                <script>
                    (function () {
                        const boutonsEtoiles = document.querySelectorAll('#selecteur-etoiles .etoile-selection');
                        const champNote = document.getElementById('champ-note');

                        function appliquerSelection(valeur) {
                            boutonsEtoiles.forEach((bouton) => {
                                const valeurBouton = Number(bouton.dataset.valeurEtoile);
                                bouton.classList.toggle('text-rouge', valeurBouton <= valeur);
                                bouton.classList.toggle('text-creme', valeurBouton > valeur);
                            });
                        }

                        boutonsEtoiles.forEach((bouton) => {
                            bouton.addEventListener('click', () => {
                                const valeur = Number(bouton.dataset.valeurEtoile);
                                champNote.value = valeur;
                                appliquerSelection(valeur);
                            });
                        });
                    })();
                </script>
            <?php endif; ?>

            <!-- Liste des avis, visible par tous -->
            <?php if (empty($avisDuProduit)): ?>
                <p class="mt-4 text-sm text-gris-chaud">Aucun avis pour le moment.</p>
            <?php else: ?>
                <div class="mt-4 space-y-3">
                    <?php foreach ($avisDuProduit as $avis): ?>
                        <div class="rounded-2xl bg-white p-4 shadow-sm">
                            <div class="flex items-center justify-between">
                                <span class="flex items-center gap-0.5"><?= etoiles_affichage($avis->getNote()) ?></span>
                                <span class="text-[11px] text-gris-chaud"><?= $avis->getDateAvis()->format('d/m/Y') ?></span>
                            </div>
                            <?php if ($avis->getCommentaire()): ?>
                                <p class="mt-2 text-sm text-charbon"><?= View::e($avis->getCommentaire()) ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

    </div>

    <!-- Panneau panier -->
    <div class="lg:col-span-1">
        <div class="sticky top-4 rounded-3xl bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-sm font-black text-charbon">Mon panier</p>
                <span class="text-xs text-gris-chaud"><?= count($lignesPanier) ?> article<?= count($lignesPanier) > 1 ? 's' : '' ?></span>
            </div>

            <?php if (empty($lignesPanier)): ?>
                <p class="mt-4 text-sm text-gris-chaud">Votre panier est vide.</p>
            <?php else: ?>
                <div class="mt-4 space-y-3">
                    <?php foreach ($lignesPanier as $ligne): ?>
                        <div class="flex items-center gap-3">
                            <img
                                src="<?= View::e($ligne['produit']->getImage() ?? '/assets/images/produit-defaut.svg') ?>"
                                alt=""
                                class="h-12 w-12 shrink-0 rounded-xl object-cover"
                            >
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-xs font-bold text-charbon"><?= View::e($ligne['produit']->getLibelle()) ?></p>
                                <p class="text-[11px] text-gris-chaud"><?= $ligne['quantite'] ?> × <?= number_format($ligne['produit']->getPrix(), 0, ',', ' ') ?> FCFA</p>
                            </div>
                            <span class="shrink-0 text-xs font-black text-rouge"><?= number_format($ligne['sousTotal'], 0, ',', ' ') ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="mt-4 flex items-center justify-between border-t border-creme pt-4">
                    <span class="text-sm font-bold text-charbon">Total</span>
                    <span class="text-lg font-black text-rouge"><?= number_format($montantTotalPanier, 0, ',', ' ') ?> FCFA</span>
                </div>

                <a href="/panier" class="mt-4 flex items-center justify-center rounded-full bg-rouge py-3 text-sm font-bold text-white transition-colors hover:bg-rouge-clair">
                    Voir le panier
                </a>
            <?php endif; ?>
        </div>
    </div>

</div>

<script>
    function ajusterQuantiteDetail(delta) {
        const input = document.getElementById('quantite-input-detail');
        const affichage = document.getElementById('quantite-affichee-detail');
        const nouvelleValeur = Math.max(1, parseInt(input.value, 10) + delta);
        input.value = nouvelleValeur;
        affichage.textContent = nouvelleValeur;
    }
</script>