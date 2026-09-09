<?php

use Core\View;

/*
 * Carte produit premium/immersive, utilisée pour le top des ventes du
 * dashboard. Composant réutilisable : reçoit ses données via $donnees,
 * ne contient aucune donnée en dur. $note et $croissance sont optionnels
 * (le backend ne les fournit pas encore à ce jour).
 *
 * Attend un tableau $donnees avec :
 * - image        (string) chemin de l'illustration
 * - nom          (string) nom du produit
 * - description  (string|null) courte description, 2 lignes max affichées
 * - ventes       (int) nombre de ventes
 * - rang         (int) position dans le classement (1, 2, 3...)
 * - note         (float|null) note moyenne
 * - croissance   (int|null) variation en % par rapport à la période précédente
 */

$image = $donnees['image'];
$nom = $donnees['nom'];
$description = $donnees['description'] ?? null;
$ventes = $donnees['ventes'];
$rang = $donnees['rang'];
$note = $donnees['note'] ?? null;
$croissance = $donnees['croissance'] ?? null;
?>
<div class="carte-produit-premium">
    <img src="<?= View::e($image) ?>" alt="<?= View::e($nom) ?>" class="carte-produit-premium__image">
    <div class="carte-produit-premium__gradient"></div>
    <div class="carte-produit-premium__reflet"></div>

    <div class="carte-produit-premium__badge">
        <?= str_pad((string) $rang, 2, '0', STR_PAD_LEFT) ?>
    </div>

    <div class="carte-produit-premium__loupe">
        <img src="<?= View::e($image) ?>" alt="">
    </div>

    <div class="absolute inset-x-0 bottom-0 p-5">
        <p class="text-[18px] font-semibold leading-snug text-ivoire"><?= View::e($nom) ?></p>

        <?php if ($description): ?>
            <p class="mt-1 line-clamp-2 text-[13px] text-or-clair/80"><?= View::e($description) ?></p>
        <?php endif; ?>

        <div class="mt-3 flex items-center gap-3 text-[13px]">
            <?php if ($note !== null): ?>
                <span class="font-medium text-or">★ <?= View::e(number_format($note, 1)) ?></span>
            <?php endif; ?>

            <span class="text-ivoire"><?= View::e((string) $ventes) ?> vendus</span>

            <?php if ($croissance !== null): ?>
                <span class="<?= $croissance >= 0 ? 'text-succes' : 'text-danger' ?>">
                    <?= $croissance >= 0 ? '↑' : '↓' ?> <?= View::e((string) abs($croissance)) ?>%
                </span>
            <?php endif; ?>
        </div>
    </div>
</div>