<?php

use Core\Session;
use Core\View;

/*
 * Layout commun à l'espace public/client (accueil, catalogue, panier,
 * profil...). Contrairement à l'espace interne, l'authentification n'est
 * pas obligatoire pour naviguer : $client est optionnel et vaut null pour
 * un visiteur non connecté, ce qui adapte silencieusement la navigation
 * (Commandes/Profil redirigent vers la connexion plutôt que de disparaître).
 */

$client ??= null;
$initialesClient = $client !== null
    ? mb_strtoupper(mb_substr($client->getPrenom(), 0, 1) . mb_substr($client->getNom(), 0, 1))
    : null;

/*
 * Le panier vit uniquement en session (voir PanierService), donc son
 * compte d'articles peut être lu directement ici sans dépendance à un
 * service complet — juste une somme de quantités, aucune logique métier.
 */
$panierBrut = Session::get('panier', []);
$nombreArticlesPanier = array_sum($panierBrut);

function icone_publique(string $nom): string
{
    $icones = [
        'accueil' => '<path d="M3 11.5 12 4l9 7.5"/><path d="M5 10v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V10"/>',
        'grille' => '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>',
        'panier' => '<circle cx="9" cy="21" r="1.4"/><circle cx="18" cy="21" r="1.4"/><path d="M3 3h2l2.4 12.2a2 2 0 0 0 2 1.6h8.2a2 2 0 0 0 2-1.6L21 7H6" stroke-linecap="round" stroke-linejoin="round"/>',
        'liste' => '<path d="M4 6h16M4 12h16M4 18h10" stroke-linecap="round"/>',
        'personne' => '<circle cx="12" cy="8" r="4"/><path d="M4 20a8 8 0 0 1 16 0"/>',
        'recherche' => '<circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3" stroke-linecap="round"/>',
        'localisation' => '<path d="M12 21s-7-6.2-7-11a7 7 0 0 1 14 0c0 4.8-7 11-7 11Z"/><circle cx="12" cy="10" r="2.5"/>',
        'etoile' => '<path d="M12 2.5 14.8 9l7 .6-5.3 4.6 1.6 6.8L12 17.6 5.9 21l1.6-6.8L2.2 9.6l7-.6L12 2.5Z"/>',
    ];

    return $icones[$nom] ?? '';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= View::e($titrePage ?? 'Saveur221') ?> · Saveur221</title>
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body class="min-h-screen bg-creme-chaude font-sans text-charbon">

    <!-- Header desktop -->
    <header class="hidden border-b border-rouge/10 bg-white lg:block">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-8 py-4">

            <a href="/" class="flex items-center gap-3">
                <img src="/assets/images/logo/saveur221-logo.png" alt="Saveur221" class="h-10 w-auto">
                <div>
                    <p class="font-voice text-xl font-black text-rouge">Saveur 221</p>
                    <p class="text-xs text-gris-chaud">Le goût du Sénégal</p>
                </div>
            </a>

            <nav class="flex items-center gap-8 text-sm font-medium">
                <a href="/" class="text-rouge">Accueil</a>
                <a href="/produits" class="transition hover:text-rouge">Menu</a>
                <a href="<?= $client ? '/commandes/historique' : '/connexion' ?>" class="transition hover:text-rouge">Commandes</a>
            </nav>

            <div class="flex items-center gap-3">
                <a href="/panier" class="relative flex h-10 w-10 items-center justify-center rounded-full border border-creme bg-white">
                    <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><?= icone_publique('panier') ?></svg>
                    <?php if ($nombreArticlesPanier > 0): ?>
                        <span class="absolute -right-1 -top-1 flex h-4.5 w-4.5 items-center justify-center rounded-full bg-rouge text-[10px] font-bold text-white">
                            <?= $nombreArticlesPanier ?>
                        </span>
                    <?php endif; ?>
                </a>

                <a href="<?= $client ? '/profil' : '/connexion' ?>" class="flex h-10 w-10 items-center justify-center rounded-full <?= $client ? 'bg-rouge text-white' : 'border border-creme bg-white' ?>">
                    <?php if ($client): ?>
                        <span class="text-xs font-black"><?= View::e($initialesClient) ?></span>
                    <?php else: ?>
                        <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><?= icone_publique('personne') ?></svg>
                    <?php endif; ?>
                </a>

                <a href="/produits" class="rounded-full bg-rouge px-5 py-2.5 text-sm font-bold text-white transition-colors hover:bg-rouge-clair">
                    Commander
                </a>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-7xl px-4 pb-28 pt-4 lg:px-8 lg:pb-12 lg:pt-8">

        <!-- Header mobile -->
        <div class="lg:hidden">
            <div class="flex items-center justify-between">
                <a href="/" class="flex items-center gap-2">
                    <img src="/assets/images/logo/saveur221-logo.png" alt="Saveur221" class="h-9 w-auto">
                    <div>
                        <p class="font-voice text-lg font-black leading-none text-rouge">Saveur 221</p>
                        <p class="mt-1 text-[9px] font-bold uppercase tracking-wide text-gris-chaud">Le goût du Sénégal</p>
                    </div>
                </a>

                <a href="<?= $client ? '/profil' : '/connexion' ?>" class="flex h-10 w-10 items-center justify-center rounded-full <?= $client ? 'bg-rouge text-white' : 'border border-creme bg-white' ?> shadow-sm">
                    <?php if ($client): ?>
                        <span class="text-xs font-black"><?= View::e($initialesClient) ?></span>
                    <?php else: ?>
                        <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><?= icone_publique('personne') ?></svg>
                    <?php endif; ?>
                </a>
            </div>

            <form method="get" action="/produits" class="mt-4 flex items-center gap-3 rounded-2xl border border-rouge/10 bg-white px-4 py-3 shadow-sm">
                <svg class="h-4.5 w-4.5 text-rouge" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><?= icone_publique('recherche') ?></svg>
                <input
                    type="text"
                    name="recherche"
                    placeholder="Rechercher un plat..."
                    class="flex-1 bg-transparent text-sm text-charbon placeholder:text-gris-chaud focus:outline-none"
                >
            </form>
        </div>

        <div class="relative">
            <?= $contenu ?>
        </div>

    </main>

    <!-- Footer -->
<footer class="hidden border-t border-rouge/10 bg-white py-16 lg:block">
    <div class="mx-auto max-w-7xl px-8">

        <div class="grid grid-cols-1 gap-10 sm:grid-cols-2 lg:grid-cols-4">

            <div>
                <a href="/" class="flex items-center gap-2.5">
                    <img src="/assets/images/logo/saveur221-logo.png" alt="Saveur221" class="h-9 w-auto">
                    <div>
                        <p class="font-voice text-lg font-black text-rouge">Saveur 221</p>
                        <p class="text-[11px] text-gris-chaud">Le goût du Sénégal</p>
                    </div>
                </a>
                <p class="mt-4 text-sm leading-relaxed text-gris-chaud">
                    Des saveurs authentiques préparées avec passion, pour une expérience culinaire unique.
                </p>
            </div>

            <div>
                <p class="text-xs font-black uppercase tracking-wide text-charbon">Navigation</p>
                <ul class="mt-4 space-y-3 text-sm text-gris-chaud">
                    <li><a href="/" class="transition hover:text-rouge">Accueil</a></li>
                    <li><a href="/produits" class="transition hover:text-rouge">Menu</a></li>
                    <li><a href="/panier" class="transition hover:text-rouge">Panier</a></li>
                    <li><a href="/commandes/historique" class="transition hover:text-rouge">Mes commandes</a></li>
                </ul>
            </div>

            <div>
                <p class="text-xs font-black uppercase tracking-wide text-charbon">Compte</p>
                <ul class="mt-4 space-y-3 text-sm text-gris-chaud">
                    <li><a href="/profil" class="transition hover:text-rouge">Mon profil</a></li>
                    <li><a href="/connexion" class="transition hover:text-rouge">Connexion</a></li>
                    <li><a href="/inscription" class="transition hover:text-rouge">Inscription</a></li>
                </ul>
            </div>

            <div>
                <p class="text-xs font-black uppercase tracking-wide text-charbon">Contact</p>
                <ul class="mt-4 space-y-3 text-sm text-gris-chaud">
                    <li class="flex items-start gap-2">
                        <svg class="mt-0.5 h-4 w-4 shrink-0 text-rouge" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                            <?= icone_publique('localisation') ?>
                        </svg>
                        <span>Dakar, Sénégal</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="h-4 w-4 shrink-0 text-rouge" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                            <path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.3 1.8.6 2.7a2 2 0 0 1-.4 2.1L8 9.9a16 16 0 0 0 6 6l1.4-1.4a2 2 0 0 1 2.1-.4c.9.3 1.8.5 2.7.6a2 2 0 0 1 1.8 2Z" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span>+221 77 000 00 00</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="h-4 w-4 shrink-0 text-rouge" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                            <rect x="3" y="5" width="18" height="14" rx="2"/>
                            <path d="M3 7l9 6 9-6" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span>contact@saveur221.sn</span>
                    </li>
                </ul>
            </div>

        </div>

        <div class="mt-10 flex flex-col items-center justify-between gap-3 border-t border-creme pt-8 sm:flex-row">
            <p class="text-xs text-gris-chaud">&copy; <?= date('Y') ?> Saveur221. Tous droits réservés.</p>
            <p class="text-xs text-gris-chaud">Fait avec passion à Dakar</p>
        </div>

    </div>
</footer>

    <!-- Navigation mobile -->
    <nav class="fixed bottom-0 left-0 right-0 z-50 border-t border-rouge/10 bg-white/95 px-4 pb-3 pt-2 backdrop-blur lg:hidden">
        <div class="mx-auto flex max-w-md items-center justify-between">

            <a href="/" class="flex flex-col items-center gap-1 text-rouge">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><?= icone_publique('accueil') ?></svg>
                <span class="text-[9px] font-bold">Accueil</span>
            </a>

            <a href="/produits" class="flex flex-col items-center gap-1 text-gris-chaud">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><?= icone_publique('grille') ?></svg>
                <span class="text-[9px] font-medium">Menu</span>
            </a>

            <a href="/panier" class="relative -mt-7 flex flex-col items-center gap-1">
                <div class="flex h-14 w-14 items-center justify-center rounded-full border-4 border-creme-chaude bg-rouge text-white shadow-lg">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><?= icone_publique('panier') ?></svg>
                </div>
                <span class="text-[9px] font-bold text-rouge">Panier</span>
                <?php if ($nombreArticlesPanier > 0): ?>
                    <span class="absolute right-0 top-0 flex h-5 w-5 items-center justify-center rounded-full bg-white text-[9px] font-black text-rouge shadow">
                        <?= $nombreArticlesPanier ?>
                    </span>
                <?php endif; ?>
            </a>

            <a href="<?= $client ? '/commandes/historique' : '/connexion' ?>" class="flex flex-col items-center gap-1 text-gris-chaud">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><?= icone_publique('liste') ?></svg>
                <span class="text-[9px] font-medium">Commandes</span>
            </a>

            <a href="<?= $client ? '/profil' : '/connexion' ?>" class="flex flex-col items-center gap-1 text-gris-chaud">
                <?php if ($client): ?>
                    <span class="flex h-5 w-5 items-center justify-center rounded-full bg-rouge text-[8px] font-black text-white"><?= View::e($initialesClient) ?></span>
                <?php else: ?>
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><?= icone_publique('personne') ?></svg>
                <?php endif; ?>
                <span class="text-[9px] font-medium">Profil</span>
            </a>

        </div>
    </nav>

</body>
</html>