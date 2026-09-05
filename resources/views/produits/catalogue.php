<?php

use Core\View;

?>
<h1>Catalogue (test)</h1>

<?php if (isset($erreur)): ?>
    <p style="color: red;"><?= View::e($erreur) ?></p>
<?php endif; ?>

<p>Nombre de produits chargés : <?= count($produits) ?></p>
<p>Nombre de catégories chargées : <?= count($categories) ?></p>

<ul>
    <?php foreach ($produits as $produit): ?>
        <li><?= View::e($produit->getLibelle()) ?> — <?= $produit->getPrix() ?> F</li>
    <?php endforeach; ?>
</ul>