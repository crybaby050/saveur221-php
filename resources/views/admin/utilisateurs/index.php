<?php

use Core\View;

$titrePage = 'Utilisateurs internes';
$section = 'utilisateurs';

function badge_role(\App\Enums\Role $role): array
{
    return $role === \App\Enums\Role::ADMIN
        ? ['Administrateur', 'bg-bordeaux/10 text-bordeaux border-bordeaux/30']
        : ['Gérant', 'bg-info/10 text-info border-info/30'];
}

function badge_statut(bool $actif): array
{
    return $actif
        ? ['Actif', 'bg-succes/10 text-succes border-succes/30']
        : ['Désactivé', 'bg-danger/10 text-danger border-danger/30'];
}
?>

<div class="space-y-6">

    <!-- Barre d'action -->
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <form method="get" action="/admin/utilisateurs" class="relative w-full sm:max-w-xs">
            <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gris-chaud" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                <circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3" stroke-linecap="round"/>
            </svg>
            <input
                type="text"
                name="recherche"
                value="<?= View::e($motCle ?? '') ?>"
                placeholder="Rechercher un utilisateur..."
                class="h-10 w-full rounded-md border border-creme bg-white pl-9 pr-3 text-sm text-charbon focus:border-bordeaux focus:outline-none focus:ring-3 focus:ring-bordeaux/10"
            >
        </form>

        <div class="flex items-center gap-3">
            <?php include __DIR__ . '/../../partials/bascule-affichage.php'; ?>
            <button type="button" onclick="ouvrirTiroirUtilisateurAjout()"
                class="inline-flex items-center justify-center gap-2 rounded-md bg-bordeaux px-4 py-2.5 text-sm font-medium text-ivoire transition-colors hover:bg-bordeaux-sombre">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                    <path d="M12 5v14M5 12h14" stroke-linecap="round"/>
                </svg>
                Nouvel utilisateur
            </button>
        </div>
    </div>

    <?php if (isset($erreur)): ?>
        <p class="rounded-md border-l-4 border-danger bg-danger/10 px-3.5 py-2.5 text-sm text-danger">
            <?= View::e($erreur) ?>
        </p>
    <?php endif; ?>

    <?php if (empty($utilisateurs)): ?>
        <div class="rounded-xl border border-dashed border-creme bg-white p-10 text-center text-sm text-gris-chaud">
            Aucun utilisateur trouvé.
        </div>
    <?php else: ?>

        <!-- Vue grille -->
        <div data-vue-contenu="grille" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            <?php foreach ($utilisateurs as $utilisateurLigne): ?>
                <?php $estSoiMeme = ($utilisateurLigne->getId() === $utilisateur->getId()); ?>
                <?php
                [$libelleRole, $classeRole] = badge_role($utilisateurLigne->getRole());
                [$libelleStatut, $classeStatut] = badge_statut($utilisateurLigne->isActif());
                $initiales = mb_strtoupper(mb_substr($utilisateurLigne->getPrenom(), 0, 1) . mb_substr($utilisateurLigne->getNom(), 0, 1));
                ?>
                <div class="rounded-xl border border-creme bg-white p-5 shadow-sm transition-shadow hover:shadow-md">
                    <div class="flex items-center gap-3">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-bordeaux text-sm font-medium text-ivoire">
                            <?= View::e($initiales) ?>
                        </div>
                        <div class="min-w-0">
                            <p class="truncate font-medium text-charbon"><?= View::e($utilisateurLigne->getPrenom() . ' ' . $utilisateurLigne->getNom()) ?></p>
                            <p class="truncate text-xs text-gris-chaud"><?= View::e($utilisateurLigne->getEmail()) ?></p>
                        </div>
                    </div>

                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        <span class="rounded-full border px-2 py-0.5 text-[11px] font-medium <?= $classeRole ?>"><?= $libelleRole ?></span>
                        <span class="rounded-full border px-2 py-0.5 text-[11px] font-medium <?= $classeStatut ?>"><?= $libelleStatut ?></span>
                    </div>

                    <div class="mt-4 flex items-center gap-2">
                        <button type="button"
                            onclick="ouvrirTiroirUtilisateurModification(this)"
                            data-utilisateur-id="<?= $utilisateurLigne->getId() ?>"
                            data-utilisateur-nom="<?= View::e($utilisateurLigne->getNom()) ?>"
                            data-utilisateur-prenom="<?= View::e($utilisateurLigne->getPrenom()) ?>"
                            data-utilisateur-email="<?= View::e($utilisateurLigne->getEmail()) ?>"
                            class="flex-1 rounded-md border border-creme px-3 py-1.5 text-center text-xs font-medium text-charbon transition-colors hover:bg-ivoire">
                            Modifier
                        </button>

                        <?php if (!$estSoiMeme): ?>
                            <form method="post" action="/admin/utilisateurs/<?= $utilisateurLigne->getId() ?>/<?= $utilisateurLigne->isActif() ? 'desactiver' : 'activer' ?>">
                                <button type="button"
                                    data-confirm-titre="<?= View::e(($utilisateurLigne->isActif() ? 'Désactiver' : 'Activer') . ' le compte') ?>"
                                    data-confirm-message="<?= View::e($utilisateurLigne->isActif()
                                        ? "Cet utilisateur ne pourra plus se connecter tant que le compte n'est pas réactivé."
                                        : "Cet utilisateur pourra à nouveau se connecter.") ?>"
                                    data-confirm-label="<?= View::e($utilisateurLigne->isActif() ? 'Désactiver' : 'Activer') ?>"
                                    data-confirm-class="<?= $utilisateurLigne->isActif() ? 'bg-danger hover:bg-danger/90' : 'bg-succes hover:bg-succes/90' ?>"
                                    class="rounded-md border border-creme px-3 py-1.5 text-xs font-medium text-charbon transition-colors hover:bg-ivoire">
                                    <?= $utilisateurLigne->isActif() ? 'Désactiver' : 'Activer' ?>
                                </button>
                            </form>
                        
                            <form method="post" action="/admin/utilisateurs/<?= $utilisateurLigne->getId() ?>/supprimer">
                                <button type="button"
                                    data-confirm-titre="Supprimer l'utilisateur"
                                    data-confirm-message="Cette action est irréversible."
                                    data-confirm-label="Supprimer"
                                    data-confirm-class="bg-danger hover:bg-danger/90"
                                    class="rounded-md border border-danger/30 px-3 py-1.5 text-xs font-medium text-danger transition-colors hover:bg-danger/10">
                                    Supprimer
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>

                    <form method="post" action="/admin/utilisateurs/<?= $utilisateurLigne->getId() ?>/role" class="mt-2">
                        <select name="role" onchange="this.form.submit()"
                            class="h-8 w-full rounded-md border border-creme bg-white px-2 text-xs text-charbon focus:border-bordeaux focus:outline-none">
                            <option value="GERANT" <?= $utilisateurLigne->getRole()->value === 'GERANT' ? 'selected' : '' ?>>Gérant</option>
                            <option value="ADMIN" <?= $utilisateurLigne->getRole()->value === 'ADMIN' ? 'selected' : '' ?>>Administrateur</option>
                        </select>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Vue tableau -->
        <div data-vue-contenu="table" class="hidden overflow-x-auto rounded-xl border border-creme bg-white shadow-sm">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-creme text-xs uppercase tracking-wide text-gris-chaud">
                        <th class="px-5 py-3 font-medium">Utilisateur</th>
                        <th class="px-5 py-3 font-medium">Rôle</th>
                        <th class="px-5 py-3 font-medium">Statut</th>
                        <th class="px-5 py-3 font-medium text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-creme">
                    <?php foreach ($utilisateurs as $utilisateurLigne): ?>
                        <?php $estSoiMeme = ($utilisateurLigne->getId() === $utilisateur->getId()); ?>
                        <?php
                        [$libelleRole, $classeRole] = badge_role($utilisateurLigne->getRole());
                        [$libelleStatut, $classeStatut] = badge_statut($utilisateurLigne->isActif());
                        $initiales = mb_strtoupper(mb_substr($utilisateurLigne->getPrenom(), 0, 1) . mb_substr($utilisateurLigne->getNom(), 0, 1));
                        ?>
                        <tr class="hover:bg-ivoire">
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-bordeaux text-xs font-medium text-ivoire">
                                        <?= View::e($initiales) ?>
                                    </div>
                                    <div>
                                        <p class="font-medium text-charbon"><?= View::e($utilisateurLigne->getPrenom() . ' ' . $utilisateurLigne->getNom()) ?></p>
                                        <p class="text-xs text-gris-chaud"><?= View::e($utilisateurLigne->getEmail()) ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3">
                                <form method="post" action="/admin/utilisateurs/<?= $utilisateurLigne->getId() ?>/role">
                                    <select name="role" onchange="this.form.submit()"
                                        class="h-8 rounded-md border border-creme bg-white px-2 text-xs text-charbon focus:border-bordeaux focus:outline-none">
                                        <option value="GERANT" <?= $utilisateurLigne->getRole()->value === 'GERANT' ? 'selected' : '' ?>>Gérant</option>
                                        <option value="ADMIN" <?= $utilisateurLigne->getRole()->value === 'ADMIN' ? 'selected' : '' ?>>Administrateur</option>
                                    </select>
                                </form>
                            </td>
                            <td class="px-5 py-3">
                                <span class="rounded-full border px-2 py-0.5 text-xs font-medium <?= $classeStatut ?>"><?= $libelleStatut ?></span>
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex justify-end gap-2">
                                    <button type="button"
                                        onclick="ouvrirTiroirUtilisateurModification(this)"
                                        data-utilisateur-id="<?= $utilisateurLigne->getId() ?>"
                                        data-utilisateur-nom="<?= View::e($utilisateurLigne->getNom()) ?>"
                                        data-utilisateur-prenom="<?= View::e($utilisateurLigne->getPrenom()) ?>"
                                        data-utilisateur-email="<?= View::e($utilisateurLigne->getEmail()) ?>"
                                        class="rounded-md border border-creme px-3 py-1.5 text-xs font-medium text-charbon transition-colors hover:bg-ivoire">
                                        Modifier
                                    </button>

                                    <?php if (!$estSoiMeme): ?>
                                        <form method="post" action="/admin/utilisateurs/<?= $utilisateurLigne->getId() ?>/<?= $utilisateurLigne->isActif() ? 'desactiver' : 'activer' ?>">
                                            <button type="button"
                                                data-confirm-titre="<?= View::e(($utilisateurLigne->isActif() ? 'Désactiver' : 'Activer') . ' le compte') ?>"
                                                data-confirm-message="<?= View::e($utilisateurLigne->isActif()
                                                    ? "Cet utilisateur ne pourra plus se connecter tant que le compte n'est pas réactivé."
                                                    : "Cet utilisateur pourra à nouveau se connecter.") ?>"
                                                data-confirm-label="<?= View::e($utilisateurLigne->isActif() ? 'Désactiver' : 'Activer') ?>"
                                                data-confirm-class="<?= $utilisateurLigne->isActif() ? 'bg-danger hover:bg-danger/90' : 'bg-succes hover:bg-succes/90' ?>"
                                                class="rounded-md border border-creme px-3 py-1.5 text-xs font-medium text-charbon transition-colors hover:bg-ivoire">
                                                <?= $utilisateurLigne->isActif() ? 'Désactiver' : 'Activer' ?>
                                            </button>
                                        </form>
                                        <form method="post" action="/admin/utilisateurs/<?= $utilisateurLigne->getId() ?>/supprimer">
                                            <button type="button"
                                                data-confirm-titre="Supprimer l'utilisateur"
                                                data-confirm-message="Cette action est irréversible."
                                                data-confirm-label="Supprimer"
                                                data-confirm-class="bg-danger hover:bg-danger/90"
                                                class="rounded-md border border-danger/30 px-3 py-1.5 text-xs font-medium text-danger transition-colors hover:bg-danger/10">
                                                Supprimer
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php
        $urlBase = '/admin/utilisateurs';
        $parametres = $motCle !== null && $motCle !== '' ? ['recherche' => $motCle] : [];
        include __DIR__ . '/../../partials/pagination.php';
        ?>

    <?php endif; ?>

    <!-- Tiroir ajout/modification utilisateur -->
    <div id="tiroir-utilisateur" class="tiroir-overlay">
        <div class="tiroir-panneau">
            <div class="flex items-center justify-between border-b border-creme px-6 py-4">
                <p id="titre-tiroir-utilisateur" class="font-voice text-lg font-medium text-charbon">Nouvel utilisateur</p>
                <button type="button" data-fermer-tiroir class="text-gris-chaud hover:text-charbon">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                        <path d="M6 6l12 12M18 6 6 18" stroke-linecap="round"/>
                    </svg>
                </button>
            </div>

            <div class="px-6 py-5">
                <form id="formulaire-tiroir-utilisateur" method="post" action="/admin/utilisateurs/ajouter" class="space-y-5">

                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <label for="tiroir-prenom" class="mb-1.5 block text-sm font-medium text-charbon">Prénom</label>
                            <input type="text" id="tiroir-prenom" name="prenom"
                                class="h-10 w-full rounded-md border border-creme bg-white px-3 text-sm text-charbon focus:border-bordeaux focus:outline-none focus:ring-3 focus:ring-bordeaux/10">
                            <p id="erreur-prenom" class="mt-1 hidden text-xs text-danger"></p>
                        </div>
                        <div>
                            <label for="tiroir-nom" class="mb-1.5 block text-sm font-medium text-charbon">Nom</label>
                            <input type="text" id="tiroir-nom" name="nom"
                                class="h-10 w-full rounded-md border border-creme bg-white px-3 text-sm text-charbon focus:border-bordeaux focus:outline-none focus:ring-3 focus:ring-bordeaux/10">
                            <p id="erreur-nom" class="mt-1 hidden text-xs text-danger"></p>
                        </div>

                        <div>
                            <label for="tiroir-email" class="mb-1.5 block text-sm font-medium text-charbon">Email</label>
                            <input type="email" id="tiroir-email" name="email"
                                class="h-10 w-full rounded-md border border-creme bg-white px-3 text-sm text-charbon focus:border-bordeaux focus:outline-none focus:ring-3 focus:ring-bordeaux/10">
                            <p id="erreur-email" class="mt-1 hidden text-xs text-danger"></p>
                        </div>

                        <div id="champ-mot-de-passe-utilisateur">
                            <label for="tiroir-mot-de-passe" class="mb-1.5 block text-sm font-medium text-charbon">Mot de passe</label>
                            <input type="password" id="tiroir-mot-de-passe" name="mot_de_passe"
                                class="h-10 w-full rounded-md border border-creme bg-white px-3 text-sm text-charbon focus:border-bordeaux focus:outline-none focus:ring-3 focus:ring-bordeaux/10">
                            <p id="erreur-mot-de-passe" class="mt-1 hidden text-xs text-danger"></p>
                        </div>

                        <div id="champ-confirmation-mot-de-passe">
                            <label for="tiroir-mot-de-passe-confirmation" class="mb-1.5 block text-sm font-medium text-charbon">Confirmer le mot de passe</label>
                            <input type="password" id="tiroir-mot-de-passe-confirmation" name="mot_de_passe_confirmation"
                                class="h-10 w-full rounded-md border border-creme bg-white px-3 text-sm text-charbon focus:border-bordeaux focus:outline-none focus:ring-3 focus:ring-bordeaux/10">
                            <p id="erreur-mot-de-passe-confirmation" class="mt-1 hidden text-xs text-danger"></p>
                        </div>

                        <div id="champ-role-utilisateur">
                            <label for="tiroir-role" class="mb-1.5 block text-sm font-medium text-charbon">Rôle</label>
                            <select id="tiroir-role" name="role"
                                class="h-10 w-full rounded-md border border-creme bg-white px-3 text-sm text-charbon focus:border-bordeaux focus:outline-none focus:ring-3 focus:ring-bordeaux/10">
                                <option value="GERANT">Gérant</option>
                                <option value="ADMIN">Administrateur</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" data-fermer-tiroir class="rounded-md border border-creme px-4 py-2.5 text-sm font-medium text-charbon transition-colors hover:bg-ivoire">
                            Annuler
                        </button>
                        <button type="button" onclick="validerEtConfirmerUtilisateur()"
                            class="rounded-md bg-bordeaux px-5 py-2.5 text-sm font-medium text-ivoire transition-colors hover:bg-bordeaux-sombre">
                            Enregistrer
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

</div>

<script>
    const tiroirUtilisateurForm = document.getElementById('formulaire-tiroir-utilisateur');
    const tiroirUtilisateurTitre = document.getElementById('titre-tiroir-utilisateur');
    const champMotDePasseUtilisateur = document.getElementById('champ-mot-de-passe-utilisateur');
    const champConfirmationMotDePasse = document.getElementById('champ-confirmation-mot-de-passe');
    const champRoleUtilisateur = document.getElementById('champ-role-utilisateur');

    let modeAjoutUtilisateur = true;

    function viderErreursUtilisateur() {
        ['prenom', 'nom', 'email', 'mot-de-passe', 'mot-de-passe-confirmation'].forEach((champ) => {
            const erreur = document.getElementById(`erreur-${champ}`);
            erreur.textContent = '';
            erreur.classList.add('hidden');
        });
    }

    function afficherErreurChamp(champ, message) {
        const erreur = document.getElementById(`erreur-${champ}`);
        erreur.textContent = message;
        erreur.classList.remove('hidden');
    }

    function validerEtConfirmerUtilisateur() {
        viderErreursUtilisateur();
        let valide = true;

        const prenom = tiroirUtilisateurForm.prenom.value.trim();
        const nom = tiroirUtilisateurForm.nom.value.trim();
        const email = tiroirUtilisateurForm.email.value.trim();

        if (prenom === '') {
            afficherErreurChamp('prenom', 'Le prénom est requis.');
            valide = false;
        }
        if (nom === '') {
            afficherErreurChamp('nom', 'Le nom est requis.');
            valide = false;
        }
        if (email === '') {
            afficherErreurChamp('email', "L'email est requis.");
            valide = false;
        } else if (!email.includes('@')) {
            afficherErreurChamp('email', 'Adresse email invalide.');
            valide = false;
        }

        if (modeAjoutUtilisateur) {
            const motDePasse = tiroirUtilisateurForm.mot_de_passe.value;
            const confirmation = tiroirUtilisateurForm.mot_de_passe_confirmation.value;

            if (motDePasse.length < 6) {
                afficherErreurChamp('mot-de-passe', 'Au moins 6 caractères requis.');
                valide = false;
            }
            if (confirmation !== motDePasse || confirmation === '') {
                afficherErreurChamp('mot-de-passe-confirmation', 'Les mots de passe ne correspondent pas.');
                valide = false;
            }
        }

        if (!valide) {
            return;
        }

        demanderConfirmation(tiroirUtilisateurForm, {
            titre: 'Enregistrer les modifications',
            message: 'Confirmer l\u2019enregistrement de cet utilisateur ?',
            confirmLabel: 'Enregistrer',
            confirmClass: 'bg-bordeaux hover:bg-bordeaux-sombre',
        });
    }

    function ouvrirTiroirUtilisateurAjout() {
        tiroirUtilisateurForm.reset();
        viderErreursUtilisateur();
        tiroirUtilisateurForm.action = '/admin/utilisateurs/ajouter';
        tiroirUtilisateurTitre.textContent = 'Nouvel utilisateur';
        modeAjoutUtilisateur = true;
        champMotDePasseUtilisateur.classList.remove('hidden');
        champConfirmationMotDePasse.classList.remove('hidden');
        champRoleUtilisateur.classList.remove('hidden');
        document.getElementById('tiroir-utilisateur').classList.add('tiroir-ouvert');
    }

    function ouvrirTiroirUtilisateurModification(bouton) {
        tiroirUtilisateurForm.reset();
        viderErreursUtilisateur();
        tiroirUtilisateurForm.action = `/admin/utilisateurs/${bouton.dataset.utilisateurId}/modifier`;
        tiroirUtilisateurForm.prenom.value = bouton.dataset.utilisateurPrenom;
        tiroirUtilisateurForm.nom.value = bouton.dataset.utilisateurNom;
        tiroirUtilisateurForm.email.value = bouton.dataset.utilisateurEmail;
        tiroirUtilisateurTitre.textContent = 'Modifier l\u2019utilisateur';
        modeAjoutUtilisateur = false;
        champMotDePasseUtilisateur.classList.add('hidden');
        champConfirmationMotDePasse.classList.add('hidden');
        champRoleUtilisateur.classList.add('hidden');
        document.getElementById('tiroir-utilisateur').classList.add('tiroir-ouvert');
    }
</script>