<?php

use App\Enums\Role;
use Core\Session;
use Core\View;

/*
 * Layout commun à tout l'espace interne (Gérant, Administrateur).
 * Attend une variable $contenu (injectée automatiquement par View::render)
 * et accepte deux variables optionnelles passées par chaque vue :
 * - $titrePage : titre affiché dans la topbar (ex. "Tableau de bord")
 * - $section   : identifiant de la section active pour la sidebar
 *                (ex. 'dashboard', 'categories', 'produits'...), utilisé
 *                pour surligner le bon lien de menu
 */

$utilisateur = Session::get('utilisateur_interne');
$role = $utilisateur?->role ?? null;
$estAdmin = $role === Role::ADMIN;

$section ??= '';
$titrePage ??= '';

// Initiales pour l'avatar de la topbar (ex. "Amadou Diop" -> "AD")
$initiales = $utilisateur
    ? mb_strtoupper(mb_substr($utilisateur->prenom, 0, 1) . mb_substr($utilisateur->nom, 0, 1))
    : '??';

$libelleRole = $estAdmin ? 'Administrateur' : 'Gérant';

/*
 * Définition des liens de menu ici plutôt que dispersés dans le HTML :
 * plus simple à étendre, et permet de générer la sidebar par une boucle
 * unique tout en gardant le contrôle sur quel rôle voit quoi.
 */
$liensMenu = [
    [
        'section' => 'dashboard',
        'label' => 'Tableau de bord',
        'href' => '/gerant/dashboard',
        'icone' => 'grille',
        'visible' => true,
    ],
    [
        'section' => 'categories',
        'label' => 'Catégories',
        'href' => '/gerant/categories',
        'icone' => 'etiquette',
        'visible' => true,
    ],
    [
        'section' => 'produits',
        'label' => 'Produits & stock',
        'href' => '/gerant/produits',
        'icone' => 'boite',
        'visible' => true,
    ],
    [
        'section' => 'commandes',
        'label' => 'Commandes',
        'href' => '/gerant/commandes',
        'icone' => 'liste',
        'visible' => true,
    ],
    [
        'section' => 'paiements',
        'label' => 'Paiements',
        'href' => '/gerant/paiements/impayees',
        'icone' => 'portefeuille',
        'visible' => true,
    ],
    [
        'section' => 'factures',
        'label' => 'Factures',
        'href' => '/gerant/factures',
        'icone' => 'document',
        'visible' => true,
    ],
    [
        'section' => 'utilisateurs',
        'label' => 'Utilisateurs',
        'href' => '/admin/utilisateurs',
        'icone' => 'utilisateurs',
        'visible' => $estAdmin,
    ],
    [
        'section' => 'clients',
        'label' => 'Clients',
        'href' => '/admin/clients',
        'icone' => 'personne',
        'visible' => $estAdmin,
    ],
    [
        'section' => 'avis',
        'label' => 'Avis',
        'href' => '/admin/avis',
        'icone' => 'etoile',
        'visible' => $estAdmin,
    ],
];

/*
 * Bibliothèque minimale d'icônes SVG inline (traits fins, cohérents avec
 * le reste du design system) — évite de dépendre d'une lib d'icônes externe
 * pour une poignée de pictos réutilisés uniquement ici.
 */
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
        'fermer' => '<path d="M6 6l12 12M18 6 6 18" stroke-linecap="round"/>',
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
<body class="font-sans bg-ivoire">

    <div class="flex min-h-screen">

        <!-- Overlay mobile, ferme la sidebar au clic en dehors -->
        <div id="overlay-sidebar" class="fixed inset-0 z-30 hidden bg-charbon/50 md:hidden"></div>

        <!-- Sidebar -->
        <aside
            id="sidebar"
            class="fixed inset-y-0 left-0 z-40 flex w-64 -translate-x-full flex-col bg-charbon transition-transform duration-200 md:static md:translate-x-0"
        >
            <div class="flex h-16 items-center gap-2.5 px-5">
                <img src="/assets/images/logo/saveur221-logo.png" alt="Saveur221" class="h-8 w-auto">
                <span class="font-voice text-lg font-medium text-ivoire">Saveur221</span>
            </div>

            <nav class="mt-4 flex-1 space-y-1 overflow-y-auto px-3">
                <?php foreach ($liensMenu as $lien): ?>
                    <?php if (!$lien['visible']) continue; ?>
                    
                        href="<?= View::e($lien['href']) ?>"
                        class="flex items-center gap-3 rounded-md px-3 py-2.5 text-sm transition-colors <?= $estActif($lien['section'])
                            ? 'bg-bordeaux text-ivoire'
                            : 'text-ivoire/70 hover:bg-ivoire/10 hover:text-ivoire' ?>"
                    >
                        <svg class="h-[18px] w-[18px] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                            <?= icone_menu($lien['icone']) ?>
                        </svg>
                        <?= View::e($lien['label']) ?>
                    </a>
                <?php endforeach; ?>
            </nav>

            <div class="border-t border-ivoire/10 p-3">
                <form method="post" action="/interne/deconnexion">
                    <button
                        type="submit"
                        class="flex w-full items-center gap-3 rounded-md px-3 py-2.5 text-sm text-ivoire/70 transition-colors hover:bg-ivoire/10 hover:text-ivoire"
                    >
                        <svg class="h-[18px] w-[18px] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                            <?= icone_menu('deconnexion') ?>
                        </svg>
                        Se déconnecter
                    </button>
                </form>
            </div>
        </aside>

        <!-- Zone principale -->
        <div class="flex flex-1 flex-col">

            <!-- Topbar -->
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

                <div class="flex items-center gap-3">
                    <div class="hidden text-right sm:block">
                        <p class="text-sm font-medium text-charbon">
                            <?= View::e(trim(($utilisateur->prenom ?? '') . ' ' . ($utilisateur->nom ?? ''))) ?>
                        </p>
                        <p class="text-xs text-gris-chaud"><?= View::e($libelleRole) ?></p>
                    </div>
                    <div class="flex h-9 w-9 items-center justify-center rounded-full bg-bordeaux text-xs font-medium text-ivoire">
                        <?= View::e($initiales) ?>
                    </div>
                </div>
            </header>

            <!-- Contenu de la page -->
            <main class="flex-1 px-5 py-6 md:px-8 md:py-8">
                <?= $contenu ?>
            </main>

        </div>
    </div>

    <script>
        // Ouverture/fermeture de la sidebar sur mobile — sur desktop (md:)
        // elle reste toujours visible via les classes Tailwind ci-dessus.
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

        boutonOuvrir.addEventListener('click', ouvrirSidebar);
        overlay.addEventListener('click', fermerSidebar);
    </script>

</body>
</html>