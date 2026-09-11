<?php

use Core\View;

$titrePage = 'Mon profil';

$initiales = mb_strtoupper(mb_substr($client->getPrenom(), 0, 1) . mb_substr($client->getNom(), 0, 1));
?>

<div class="mx-auto max-w-2xl">

    <div class="flex items-center gap-4">
        <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-rouge text-lg font-black text-white">
            <?= View::e($initiales) ?>
        </div>
        <div>
            <h1 class="font-voice text-xl font-black text-charbon"><?= View::e(trim($client->getPrenom() . ' ' . $client->getNom())) ?></h1>
            <p class="text-sm text-gris-chaud"><?= View::e($client->getEmail()) ?></p>
        </div>
    </div>

    <?php if (isset($erreur)): ?>
        <p class="mt-5 rounded-2xl border-l-4 border-rouge bg-rouge/10 px-4 py-3 text-sm text-rouge">
            <?= View::e($erreur) ?>
        </p>
    <?php endif; ?>

    <!-- Informations personnelles -->
    <div class="mt-6 rounded-3xl bg-white p-6 shadow-sm">
        <p class="text-sm font-bold text-charbon">Informations personnelles</p>

        <form method="post" action="/profil" class="mt-4 flex flex-col gap-4">

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="prenom" class="mb-1.5 block text-sm text-charbon">Prénom</label>
                    <input
                        type="text"
                        id="prenom"
                        name="prenom"
                        value="<?= View::e($client->getPrenom()) ?>"
                        required
                        class="h-11 w-full rounded-xl border border-creme bg-white px-3.5 text-sm text-charbon focus:border-rouge focus:outline-none focus:ring-3 focus:ring-rouge/10"
                    >
                </div>
                <div>
                    <label for="nom" class="mb-1.5 block text-sm text-charbon">Nom</label>
                    <input
                        type="text"
                        id="nom"
                        name="nom"
                        value="<?= View::e($client->getNom()) ?>"
                        required
                        class="h-11 w-full rounded-xl border border-creme bg-white px-3.5 text-sm text-charbon focus:border-rouge focus:outline-none focus:ring-3 focus:ring-rouge/10"
                    >
                </div>
            </div>

            <div>
                <label for="email" class="mb-1.5 block text-sm text-charbon">Email</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="<?= View::e($client->getEmail()) ?>"
                    required
                    class="h-11 w-full rounded-xl border border-creme bg-white px-3.5 text-sm text-charbon focus:border-rouge focus:outline-none focus:ring-3 focus:ring-rouge/10"
                >
            </div>

            <div>
                <label for="telephone" class="mb-1.5 block text-sm text-charbon">Téléphone</label>
                <input
                    type="tel"
                    id="telephone"
                    name="telephone"
                    value="<?= View::e($client->getTelephone() ?? '') ?>"
                    placeholder="77 123 45 67"
                    class="h-11 w-full rounded-xl border border-creme bg-white px-3.5 text-sm text-charbon focus:border-rouge focus:outline-none focus:ring-3 focus:ring-rouge/10"
                >
            </div>

            <div>
                <label for="adresse" class="mb-1.5 block text-sm text-charbon">Adresse</label>
                <input
                    type="text"
                    id="adresse"
                    name="adresse"
                    value="<?= View::e($client->getAdresse() ?? '') ?>"
                    class="h-11 w-full rounded-xl border border-creme bg-white px-3.5 text-sm text-charbon focus:border-rouge focus:outline-none focus:ring-3 focus:ring-rouge/10"
                >
            </div>

            <button type="submit" class="mt-2 rounded-full bg-rouge py-3 text-sm font-bold text-white transition-colors hover:bg-rouge-clair">
                Enregistrer les modifications
            </button>
        </form>
    </div>

    <!-- Changement de mot de passe -->
    <div class="mt-4 rounded-3xl bg-white p-6 shadow-sm">
        <p class="text-sm font-bold text-charbon">Changer de mot de passe</p>

        <form method="post" action="/profil/mot-de-passe" class="mt-4 flex flex-col gap-4">
            <div>
                <label for="mot_de_passe" class="mb-1.5 block text-sm text-charbon">Nouveau mot de passe</label>
                <div style="position:relative;">
                    <input
                        type="password"
                        id="mot_de_passe"
                        name="mot_de_passe"
                        placeholder="6 caractères minimum"
                        required
                        minlength="6"
                        class="h-11 w-full rounded-xl border border-creme bg-white px-3.5 text-sm text-charbon focus:border-rouge focus:outline-none focus:ring-3 focus:ring-rouge/10"
                        style="padding-right:42px;"
                    >
                    <button
                        type="button"
                        id="toggle-mot-de-passe"
                        aria-label="Afficher le mot de passe"
                        style="position:absolute; right:12px; top:50%; transform:translateY(-50%); background:none; border:none; padding:0; cursor:pointer; color:#8A7F6E; line-height:0;"
                    >
                        <svg id="icone-oeil-ouvert" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true" style="width:18px; height:18px;">
                            <path d="M1.5 12S5 5 12 5s10.5 7 10.5 7-3.5 7-10.5 7S1.5 12 1.5 12Z" stroke-linecap="round" stroke-linejoin="round"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                        <svg id="icone-oeil-ferme" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true" style="width:18px; height:18px; display:none;">
                            <path d="M3 3l18 18M10.6 10.6a3 3 0 0 0 4.24 4.24M9.9 5.1A10.9 10.9 0 0 1 12 5c7 0 10.5 7 10.5 7a13.5 13.5 0 0 1-3.1 3.9M6.1 6.6C3.6 8.3 1.5 12 1.5 12s3.5 7 10.5 7c1.3 0 2.5-.2 3.6-.6" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="rounded-full border border-creme py-3 text-sm font-bold text-charbon transition-colors hover:bg-creme">
                Mettre à jour le mot de passe
            </button>
        </form>
    </div>

    <!-- Déconnexion -->
    <form method="post" action="/deconnexion" class="mt-4">
        <button type="submit" class="w-full rounded-full border border-rouge/20 py-3 text-sm font-bold text-rouge transition-colors hover:bg-rouge/5">
            Se déconnecter
        </button>
    </form>

</div>

<script>
    const boutonBascule = document.getElementById('toggle-mot-de-passe');
    const champMotDePasse = document.getElementById('mot_de_passe');
    const iconeOeilOuvert = document.getElementById('icone-oeil-ouvert');
    const iconeOeilFerme = document.getElementById('icone-oeil-ferme');

    boutonBascule.addEventListener('click', () => {
        const estVisible = champMotDePasse.type === 'text';

        champMotDePasse.type = estVisible ? 'password' : 'text';
        iconeOeilOuvert.style.display = estVisible ? '' : 'none';
        iconeOeilFerme.style.display = estVisible ? 'none' : '';
        boutonBascule.setAttribute(
            'aria-label',
            estVisible ? 'Afficher le mot de passe' : 'Masquer le mot de passe'
        );
    });
</script>