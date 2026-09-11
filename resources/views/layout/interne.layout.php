<?php

use App\Enums\Role;
//use Core\Session;
use Core\View;
use App\Services\AuthService;

/*
 * Layout commun à tout l'espace interne (Gérant, Administrateur).
 * Attend une variable $contenu (injectée automatiquement par View::render)
 * et accepte deux variables optionnelles passées par chaque vue :
 * - $titrePage : titre affiché dans la topbar (ex. "Tableau de bord")
 * - $section   : identifiant de la section active pour la sidebar
 */

$role = $utilisateur?->getRole();
$estAdmin = $role === Role::ADMIN;

$section ??= '';
$titrePage ??= '';

$initiales = $utilisateur
    ? mb_strtoupper(mb_substr($utilisateur->getPrenom(), 0, 1) . mb_substr($utilisateur->getNom(), 0, 1))
    : '??';

$libelleRole = $estAdmin ? 'Administrateur' : 'Gérant';

$liensMenu = [
    ['section' => 'dashboard', 'label' => 'Tableau de bord', 'href' => '/gerant/dashboard', 'icone' => 'grille', 'visible' => true],
    ['section' => 'categories', 'label' => 'Catégories', 'href' => '/gerant/categories', 'icone' => 'etiquette', 'visible' => true],
    ['section' => 'produits', 'label' => 'Produits & stock', 'href' => '/gerant/produits', 'icone' => 'boite', 'visible' => true],
    ['section' => 'commandes', 'label' => 'Commandes', 'href' => '/gerant/commandes', 'icone' => 'liste', 'visible' => true],
    ['section' => 'paiements', 'label' => 'Paiements', 'href' => '/gerant/paiements/impayees', 'icone' => 'portefeuille', 'visible' => true],
    ['section' => 'factures', 'label' => 'Factures', 'href' => '/gerant/factures', 'icone' => 'document', 'visible' => true],
    ['section' => 'utilisateurs', 'label' => 'Utilisateurs', 'href' => '/admin/utilisateurs', 'icone' => 'utilisateurs', 'visible' => $estAdmin],
    ['section' => 'clients', 'label' => 'Clients', 'href' => '/admin/clients', 'icone' => 'personne', 'visible' => $estAdmin],
    ['section' => 'avis', 'label' => 'Avis', 'href' => '/admin/avis', 'icone' => 'etoile', 'visible' => $estAdmin],
];

function icone_menu(string $nom): string
{
    $icones = [
        'grille' => '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>',
        'etiquette' => '<path d="M12 2 21 11l-9 9-8.5-8.5A2 2 0 0 1 3 10V4a2 2 0 0 1 2-2h6a2 2 0 0 1 1 .3Z"/><circle cx="7.5" cy="7.5" r="1"/>',
        'boite' => '<path d="M21 8 12 3 3 8l9 5 9-5Z"/><path d="M3 8v9l9 5 9-5V8"/><path d="M12 13v9"/>',
        'liste' => '<path d="M4 6h16M4 12h16M4 18h10" stroke-linecap="round"/>',
        'portefeuille' => '<rect x="3" y="6" width="18" height="13" rx="2"/><path d="M3 10h18"/><circle cx="16" cy="14" r="1"/>',
        'document' => '<path d="M7 3h7l5 5v13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1Z"/><path d="M14 3v5h5"/>',
        'utilisateurs' => '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20a6.5 6.5 0 0 1 13 0"/><circle cx="17.5" cy="9" r="2.8"/><path d="M15.5 13.5A5.2 5.2 0 0 1 21.5 19"/>',
        'personne' => '<circle cx="12" cy="8" r="4"/><path d="M4 20a8 8 0 0 1 16 0"/>',
        'etoile' => '<path d="M12 2.5 14.8 9l7 .6-5.3 4.6 1.6 6.8L12 17.6 5.9 21l1.6-6.8L2.2 9.6l7-.6L12 2.5Z"/>',
        'deconnexion' => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" stroke-linecap="round"/><path d="M16 17l5-5-5-5" stroke-linecap="round" stroke-linejoin="round"/><path d="M21 12H9" stroke-linecap="round"/>',
        'menu' => '<path d="M4 7h16M4 12h16M4 17h16" stroke-linecap="round"/>',
    ];

    return $icones[$nom] ?? '';
}

$estActif = static fn(string $cle): bool => $section === $cle;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= View::e($titrePage) ?> · Espace interne · Saveur221</title>
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body class="font-sans bg-white">

    <div class="flex min-h-screen">

        <div id="overlay-sidebar" class="fixed inset-0 z-30 hidden bg-charbon/50 md:hidden"></div>

        <!-- Sidebar : fond charbon, motifs Art déco fins en coins pour rejoindre
            l'identité déjà posée sur la page de connexion, plutôt qu'un noir plat. -->
        <aside
            id="sidebar"
            class="fixed inset-y-0 left-0 z-40 flex w-64 -translate-x-full flex-col overflow-hidden bg-charbon transition-transform duration-200 md:translate-x-0"
        >
            <svg class="pointer-events-none absolute inset-0 h-full w-full opacity-40" viewBox="0 0 256 700" preserveAspectRatio="none" aria-hidden="true">
                <g stroke="#C9A45C" stroke-width="1" fill="none">
                    <path d="M0,0 L36,0 L36,36" opacity="0.5"/>
                    <path d="M256,0 L220,0 L220,36" opacity="0.5"/>
                    <path d="M0,700 L36,700 L36,664" opacity="0.5"/>
                    <path d="M256,700 L220,700 L220,664" opacity="0.5"/>
                </g>
            </svg>

            <div class="relative flex h-16 items-center gap-2.5 border-b border-ivoire/10 px-5">
                <img src="/assets/images/logo/saveur221-logo.png" alt="Saveur221" class="h-8 w-auto">
                <span class="font-voice text-lg font-medium text-ivoire">Saveur221</span>
            </div>

            <nav class="relative mt-4 flex-1 space-y-1 overflow-y-auto px-3">
                <?php foreach ($liensMenu as $lien): ?>
                    <?php if (!$lien['visible']) continue; ?>

                        <a href="<?= View::e($lien['href']) ?>"
                        class="group flex items-center gap-3 rounded-md border-l-2 px-3 py-2.5 text-sm transition-colors <?= $estActif($lien['section'])
                            ? 'border-or bg-bordeaux text-ivoire'
                            : 'border-transparent text-ivoire/65 hover:border-or/40 hover:bg-ivoire/5 hover:text-ivoire' ?>"
                    >
                        <svg class="h-[18px] w-[18px] shrink-0 <?= $estActif($lien['section']) ? 'text-or-clair' : 'text-ivoire/50 group-hover:text-or-clair' ?>" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                            <?= icone_menu($lien['icone']) ?>
                        </svg>
                        <?= View::e($lien['label']) ?>
                    </a>
                <?php endforeach; ?>
            </nav>

            <div class="relative border-t border-ivoire/10 p-3">
                <form method="post" action="/interne/deconnexion">
                    <button
                        type="submit"
                        class="flex w-full items-center gap-3 rounded-md px-3 py-2.5 text-sm text-ivoire/65 transition-colors hover:bg-ivoire/5 hover:text-ivoire"
                    >
                        <svg class="h-[18px] w-[18px] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                            <?= icone_menu('deconnexion') ?>
                        </svg>
                        Se déconnecter
                    </button>
                </form>
            </div>
        </aside>

        <div class="flex min-w-0 flex-1 flex-col md:ml-64">

            <header class="flex h-16 items-center justify-between border-b border-or-clair bg-white px-5 md:px-8">
                <div class="flex items-center gap-3">
                    <button
                        id="bouton-ouvrir-sidebar"
                        type="button"
                        aria-label="Ouvrir le menu"
                        class="text-gris-chaud hover:text-charbon md:hidden"
                    >
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                            <?= icone_menu('menu') ?>
                        </svg>
                    </button>
                    <h1 class="font-voice text-lg font-medium text-charbon md:text-xl">
                        <?= View::e($titrePage) ?>
                    </h1>
                </div>

                <div class="relative">
                    <button
                        id="bouton-menu-utilisateur"
                        type="button"
                        class="flex items-center gap-3 rounded-md px-2 py-1.5 transition-colors hover:bg-ivoire"
                    >
                        <div class="hidden text-right sm:block">
                            <p class="text-sm font-medium text-charbon">
                                <?= View::e($utilisateur ? trim($utilisateur->getPrenom() . ' ' . $utilisateur->getNom()) : '') ?>
                            </p>
                            <p class="text-xs text-gris-chaud"><?= View::e($libelleRole) ?></p>
                        </div>
                        <div class="flex h-9 w-9 items-center justify-center rounded-full bg-bordeaux text-xs font-medium text-ivoire ring-2 ring-or-clair/50">
                            <?= View::e($initiales) ?>
                        </div>
                        <svg class="h-4 w-4 text-gris-chaud" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                            <path d="M6 9l6 6 6-6" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>

                    <div
                        id="menu-utilisateur"
                        class="absolute right-0 top-full z-50 mt-2 hidden w-52 overflow-hidden rounded-lg border border-creme bg-white shadow-lg"
                    >
                        <a href="/interne/profil" class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-charbon transition-colors hover:bg-ivoire">
                            <svg class="h-4 w-4 text-gris-chaud" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                                <circle cx="12" cy="8" r="4"/><path d="M4 20a8 8 0 0 1 16 0"/>
                            </svg>
                            Modifier mon profil
                        </a>
                        <form method="post" action="/interne/deconnexion">
                            <button
                                type="submit"
                                class="flex w-full items-center gap-2.5 border-t border-creme px-4 py-2.5 text-left text-sm text-charbon transition-colors hover:bg-ivoire"
                            >
                                <svg class="h-4 w-4 text-gris-chaud" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                                    <?= icone_menu('deconnexion') ?>
                                </svg>
                                Se déconnecter
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <!-- Contenu : léger motif de fond (assiette) très discret, cohérent
                 avec le traitement décoratif déjà utilisé sur la connexion. -->
            <main class="relative min-w-0 flex-1 overflow-hidden px-5 py-6 md:px-8 md:py-8">
                <svg class="pointer-events-none absolute -bottom-16 -right-16 h-64 w-64 text-bordeaux opacity-[0.03]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="0.6" aria-hidden="true">
                    <circle cx="12" cy="12" r="9"/>
                    <circle cx="12" cy="12" r="5"/>
                </svg>
                <div class="relative">
                    <?= $contenu ?>
                </div>
            </main>

        </div>


    </div>

    <!-- Modal de confirmation de suppression, partagé par tout l'espace interne -->
<!-- Modal de confirmation générique, partagé par tout l'espace interne -->
<div id="modal-confirmation" class="fixed inset-0 z-50 hidden items-center justify-center bg-charbon/50 p-4">
    <div class="w-full max-w-sm rounded-xl bg-white p-6 shadow-xl">
        <p id="modal-confirmation-titre" class="font-voice text-lg font-medium text-charbon">Confirmer l'action</p>
        <p id="modal-confirmation-message" class="mt-2 text-sm text-gris-chaud">
            Cette action est irréversible.
        </p>
        <div class="mt-5 flex justify-end gap-3">
            <button type="button" id="modal-confirmation-annuler"
                class="rounded-md border border-creme px-4 py-2 text-sm font-medium text-charbon transition-colors hover:bg-ivoire">
                Annuler
            </button>
            <button type="button" id="modal-confirmation-confirmer"
                class="rounded-md bg-bordeaux px-4 py-2 text-sm font-medium text-ivoire transition-colors hover:bg-bordeaux-sombre">
                Confirmer
            </button>
        </div>
    </div>
</div>

<script>
    let formulaireConfirmationActuel = null;

/**
 * Ouvre le modal de confirmation partagé avant soumission d'un formulaire.
 * options.titre, options.message, options.confirmLabel et
 * options.confirmClass permettent d'adapter le texte et la couleur du
 * bouton selon l'action (suppression en rouge, modification en violet...).
 */
function demanderConfirmation(formulaire, options = {}) {
    formulaireConfirmationActuel = formulaire;

    document.getElementById('modal-confirmation-titre').textContent = options.titre || 'Confirmer l\'action';
    document.getElementById('modal-confirmation-message').textContent = options.message || 'Cette action est irréversible.';

    const boutonConfirmer = document.getElementById('modal-confirmation-confirmer');
    boutonConfirmer.textContent = options.confirmLabel || 'Confirmer';
    boutonConfirmer.className = 'rounded-md px-4 py-2 text-sm font-medium text-ivoire transition-colors '
        + (options.confirmClass || 'bg-bordeaux hover:bg-bordeaux-sombre');

    document.getElementById('modal-confirmation').classList.remove('hidden');
    document.getElementById('modal-confirmation').classList.add('flex');
}

function demanderSuppression(formulaire, message) {
    demanderConfirmation(formulaire, {
        titre: 'Confirmer la suppression',
        message: message || 'Cette action est irréversible.',
        confirmLabel: 'Supprimer',
        confirmClass: 'bg-danger hover:bg-danger/90'
    });
}

function fermerModalConfirmation() {
    formulaireConfirmationActuel = null;
    document.getElementById('modal-confirmation').classList.add('hidden');
    document.getElementById('modal-confirmation').classList.remove('flex');
}

// Lit les data-confirm-* d'un bouton plutôt que de générer du JS depuis
// PHP — évite tout risque de casse sur les apostrophes des textes français.
document.querySelectorAll('[data-confirm-titre]').forEach((bouton) => {
    bouton.addEventListener('click', () => {
        demanderConfirmation(bouton.closest('form'), {
            titre: bouton.dataset.confirmTitre,
            message: bouton.dataset.confirmMessage,
            confirmLabel: bouton.dataset.confirmLabel,
            confirmClass: bouton.dataset.confirmClass,
        });
    });
});

document.getElementById('modal-confirmation-annuler').addEventListener('click', fermerModalConfirmation);

document.getElementById('modal-confirmation-confirmer').addEventListener('click', () => {
    if (formulaireConfirmationActuel) {
        formulaireConfirmationActuel.submit();
    }
});
</script>

    <script>
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay-sidebar');
        const boutonOuvrir = document.getElementById('bouton-ouvrir-sidebar');

        function ouvrirSidebar() {
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.remove('hidden');
        }

        function fermerSidebar() {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
        }

        const boutonMenuUtilisateur = document.getElementById('bouton-menu-utilisateur');
        const menuUtilisateur = document.getElementById('menu-utilisateur');
                        
        boutonMenuUtilisateur.addEventListener('click', (evenement) => {
            evenement.stopPropagation();
            menuUtilisateur.classList.toggle('hidden');
        });
                        
        document.addEventListener('click', (evenement) => {
            if (!menuUtilisateur.contains(evenement.target) && !boutonMenuUtilisateur.contains(evenement.target)) {
                menuUtilisateur.classList.add('hidden');
            }
        });

        // Bascule grille/tableau, réutilisée sur toutes les pages de listing.
// Chaque contenu concerné doit porter data-vue-contenu="grille" ou
// data-vue-contenu="table" ; les boutons portent data-bascule-vue de
// même valeur. Préférence mémorisée par utilisateur, appliquée par
// défaut sur chaque nouvelle page de liste.
const boutonsVue = document.querySelectorAll('[data-bascule-vue]');
const conteneursVue = document.querySelectorAll('[data-vue-contenu]');

function appliquerVue(vue) {
    conteneursVue.forEach((conteneur) => {
        conteneur.classList.toggle('hidden', conteneur.dataset.vueContenu !== vue);
    });
    boutonsVue.forEach((bouton) => {
        const actif = bouton.dataset.basculeVue === vue;
        bouton.classList.toggle('bg-bordeaux', actif);
        bouton.classList.toggle('text-ivoire', actif);
        bouton.classList.toggle('text-gris-chaud', !actif);
    });
    localStorage.setItem('affichage-liste', vue);
}

if (boutonsVue.length > 0) {
    boutonsVue.forEach((bouton) => {
        bouton.addEventListener('click', () => appliquerVue(bouton.dataset.basculeVue));
    });

    appliquerVue(localStorage.getItem('affichage-liste') || 'grille');
}

function ouvrirTiroir(id) {
    const tiroir = document.getElementById(id);

    if (tiroir) {
        tiroir.classList.add('tiroir-ouvert');
    }
}

function fermerTiroir(tiroir) {
    tiroir?.classList.remove('tiroir-ouvert');
}

document.querySelectorAll('[data-fermer-tiroir]').forEach((declencheur) => {
    declencheur.addEventListener('click', () => {
        fermerTiroir(declencheur.closest('.tiroir-overlay'));
    });
});

document.querySelectorAll('.tiroir-overlay').forEach((overlay) => {
    overlay.addEventListener('click', (evenement) => {
        if (evenement.target === overlay) {
            fermerTiroir(overlay);
        }
    });
});


        boutonOuvrir.addEventListener('click', ouvrirSidebar);
        overlay.addEventListener('click', fermerSidebar);
    </script>

</body>
</html>