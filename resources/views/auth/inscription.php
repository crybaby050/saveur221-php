<?php

use Core\View;

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription · Saveur221</title>
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body class="font-sans">

    <div class="flex min-h-screen flex-col md:h-screen md:flex-row">

        <div class="relative min-h-[220px] md:min-h-0 flex-1 overflow-hidden bg-charbon">
            <video
                autoplay
                muted
                loop
                playsinline
                poster="/assets/images/salle-restaurant.jpg"
                class="absolute inset-0 h-full w-full object-cover [animation:zoomLent_30s_ease-in-out_infinite_alternate]"
            >
                <source src="/assets/videos/salle-restaurant.mp4" type="video/mp4">
            </video>
            <div class="absolute inset-0 bg-charbon/40"></div>

            <svg class="absolute inset-0 h-full w-full" viewBox="0 0 340 440" preserveAspectRatio="none" aria-hidden="true">
                <g stroke="#C9A45C" stroke-width="1" fill="none" opacity="0.5">
                    <path d="M20,20 L60,20 L60,60"></path>
                    <path d="M320,20 L280,20 L280,60"></path>
                    <path d="M20,420 L60,420 L60,380"></path>
                    <path d="M320,420 L280,420 L280,380"></path>
                </g>
            </svg>

            <div class="absolute inset-x-12 bottom-12">
                <p class="mb-1.5 font-voice text-2xl font-medium text-ivoire">Rejoignez-nous</p>
                <p class="text-sm tracking-wide text-or-clair">créez votre compte en un instant</p>
            </div>
        </div>

        <div class="relative flex flex-1 items-center justify-center overflow-hidden bg-ivoire px-11 py-12">

            <svg
                aria-hidden="true"
                viewBox="0 0 24 24" fill="none" stroke="#C9A45C" stroke-width="1"
                style="position:absolute; top:-20px; left:-20px; width:130px; height:130px; opacity:0.07; z-index:0;"
            >
                <path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M7 2v20" stroke-linecap="round"/>
                <path d="M21 15V2a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3Zm0 0v7" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>

            <svg
                aria-hidden="true"
                viewBox="0 0 24 24" fill="none" stroke="#7A2334" stroke-width="0.6"
                style="position:absolute; bottom:-40px; right:-40px; width:180px; height:180px; opacity:0.06; z-index:0;"
            >
                <circle cx="12" cy="12" r="9"/>
                <circle cx="12" cy="12" r="5"/>
            </svg>

            <div class="relative w-full max-w-[400px]" style="z-index:1;">
                <a href="/" class="mb-4 inline-flex items-center gap-1.5 text-sm text-gris-chaud transition-colors hover:text-bordeaux">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                        <path d="M15 18l-6-6 6-6" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Retour à l'accueil
                </a>

                <div class="mb-6 flex justify-center">
                    <img
                        src="/assets/images/logo/saveur221-logo.png"
                        alt="Saveur221"
                        class="h-20 w-auto"
                    >
                </div>

                <h1 class="mb-1 text-center font-voice text-2xl font-medium text-charbon">Créer un compte</h1>
                <p class="mb-6 text-center text-sm text-gris-chaud">Rejoignez Saveur221 pour commander</p>

                <?php if (isset($erreur)): ?>
                    <p class="mb-5 rounded-md border-l-4 border-bordeaux bg-bordeaux/10 px-3.5 py-2.5 text-sm text-bordeaux">
                        <?= View::e($erreur) ?>
                    </p>
                <?php endif; ?>

                <form method="post" action="/inscription" id="formulaire-inscription" class="flex flex-col gap-1" novalidate>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="prenom" class="mb-1.5 block text-sm text-charbon">Prénom</label>
                            <input
                                type="text"
                                id="prenom"
                                name="prenom"
                                class="h-11 w-full rounded-md border border-or bg-white px-3.5 text-sm text-charbon focus:border-bordeaux focus:outline-none focus:ring-3 focus:ring-bordeaux/10"
                            >
                            <p id="erreur-prenom" class="mt-1 hidden text-xs text-bordeaux"></p>
                        </div>
                        <div>
                            <label for="nom" class="mb-1.5 block text-sm text-charbon">Nom</label>
                            <input
                                type="text"
                                id="nom"
                                name="nom"
                                class="h-11 w-full rounded-md border border-or bg-white px-3.5 text-sm text-charbon focus:border-bordeaux focus:outline-none focus:ring-3 focus:ring-bordeaux/10"
                            >
                            <p id="erreur-nom" class="mt-1 hidden text-xs text-bordeaux"></p>
                        </div>
                    </div>

                    <div class="mt-3">
                        <label for="email" class="mb-1.5 block text-sm text-charbon">Email</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            placeholder="vous@exemple.com"
                            class="h-11 w-full rounded-md border border-or bg-white px-3.5 text-sm text-charbon focus:border-bordeaux focus:outline-none focus:ring-3 focus:ring-bordeaux/10"
                        >
                        <p id="erreur-email" class="mt-1 hidden text-xs text-bordeaux"></p>
                    </div>

                    <div class="mt-3">
                        <label for="telephone" class="mb-1.5 block text-sm text-charbon">Téléphone <span class="text-gris-chaud">(facultatif)</span></label>
                        <input
                            type="tel"
                            id="telephone"
                            name="telephone"
                            placeholder="77 123 45 67"
                            class="h-11 w-full rounded-md border border-or bg-white px-3.5 text-sm text-charbon focus:border-bordeaux focus:outline-none focus:ring-3 focus:ring-bordeaux/10"
                        >
                    </div>

                    <div class="mt-3">
                        <label for="adresse" class="mb-1.5 block text-sm text-charbon">Adresse <span class="text-gris-chaud">(facultatif)</span></label>
                        <input
                            type="text"
                            id="adresse"
                            name="adresse"
                            class="h-11 w-full rounded-md border border-or bg-white px-3.5 text-sm text-charbon focus:border-bordeaux focus:outline-none focus:ring-3 focus:ring-bordeaux/10"
                        >
                    </div>

                    <div class="mt-3">
                        <label for="mot_de_passe" class="mb-1.5 block text-sm text-charbon">Mot de passe</label>
                        <div style="position:relative;">
                            <input
                                type="password"
                                id="mot_de_passe"
                                name="mot_de_passe"
                                placeholder="6 caractères minimum"
                                class="h-11 w-full rounded-md border border-or bg-white px-3.5 text-sm text-charbon focus:border-bordeaux focus:outline-none focus:ring-3 focus:ring-bordeaux/10"
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
                        <p id="erreur-mot-de-passe" class="mt-1 hidden text-xs text-bordeaux"></p>
                    </div>

                    <button
                        type="submit"
                        class="mt-4 h-[46px] rounded-md bg-bordeaux text-sm font-medium tracking-wide text-ivoire transition-colors hover:bg-bordeaux-sombre"
                    >
                        Créer mon compte
                    </button>
                </form>

                <p class="mt-5 text-center text-sm text-gris-chaud">
                    Déjà inscrit ? <a href="/connexion" class="font-medium text-bordeaux hover:underline">Se connecter</a>
                </p>

            </div>
        </div>

    </div>

<script>
    const formulaireInscription = document.getElementById('formulaire-inscription');
    const champPrenom = document.getElementById('prenom');
    const champNom = document.getElementById('nom');
    const champEmailInscription = document.getElementById('email');
    const champMdpInscription = document.getElementById('mot_de_passe');

    function afficherErreurInscription(champId, message) {
        const erreur = document.getElementById(`erreur-${champId}`);
        erreur.textContent = message;
        erreur.classList.remove('hidden');
    }

    function viderErreursInscription() {
        ['prenom', 'nom', 'email', 'mot-de-passe'].forEach((champ) => {
            const erreur = document.getElementById(`erreur-${champ}`);
            erreur.textContent = '';
            erreur.classList.add('hidden');
        });
    }

    formulaireInscription.addEventListener('submit', (evenement) => {
        viderErreursInscription();
        let valide = true;

        if (champPrenom.value.trim() === '') {
            afficherErreurInscription('prenom', 'Le prénom est requis.');
            valide = false;
        }

        if (champNom.value.trim() === '') {
            afficherErreurInscription('nom', 'Le nom est requis.');
            valide = false;
        }

        if (champEmailInscription.value.trim() === '') {
            afficherErreurInscription('email', "L'email est requis.");
            valide = false;
        } else if (!champEmailInscription.value.includes('@')) {
            afficherErreurInscription('email', 'Adresse email invalide.');
            valide = false;
        }

        if (champMdpInscription.value.length < 6) {
            afficherErreurInscription('mot-de-passe', 'Le mot de passe doit contenir au moins 6 caractères.');
            valide = false;
        }

        if (!valide) {
            evenement.preventDefault();
        }
    });
</script>

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

</body>
</html>