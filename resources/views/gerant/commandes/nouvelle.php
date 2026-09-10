<?php

use Core\View;

$titrePage = 'Nouvelle commande';
$section = 'commandes';

$produitsJson = json_encode(array_map(
    fn($produit) => [
        'id' => $produit->getId(),
        'libelle' => $produit->getLibelle(),
        'prix' => $produit->getPrix(),
        'stock' => $produit->getQuantiteStock(),
        'image' => $produit->getImage() ?? '/assets/images/produit-defaut.svg',
    ],
    $produits
), JSON_THROW_ON_ERROR);
?>

<div class="space-y-6">

    <a href="/gerant/commandes" class="inline-flex items-center gap-1.5 text-sm text-gris-chaud transition-colors hover:text-charbon">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
            <path d="M15 18l-6-6 6-6" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        Retour aux commandes
    </a>

    <?php if (isset($erreur)): ?>
        <p class="rounded-md border-l-4 border-danger bg-danger/10 px-3.5 py-2.5 text-sm text-danger">
            <?= View::e($erreur) ?>
        </p>
    <?php endif; ?>

    <!-- Indicateur d'étapes -->
    <div class="flex items-center justify-center gap-2 sm:gap-4">
        <div data-etape-indicateur="1" class="flex items-center gap-2">
            <span data-cercle-etape class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border-2 border-bordeaux bg-bordeaux text-sm font-medium text-ivoire">1</span>
            <span data-libelle-etape class="hidden text-sm font-medium text-charbon sm:inline">Client</span>
        </div>
        <div class="h-px w-8 bg-creme sm:w-16"></div>
        <div data-etape-indicateur="2" class="flex items-center gap-2">
            <span data-cercle-etape class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border-2 border-creme bg-white text-sm font-medium text-gris-chaud">2</span>
            <span data-libelle-etape class="hidden text-sm font-medium text-gris-chaud sm:inline">Produits</span>
        </div>
        <div class="h-px w-8 bg-creme sm:w-16"></div>
        <div data-etape-indicateur="3" class="flex items-center gap-2">
            <span data-cercle-etape class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border-2 border-creme bg-white text-sm font-medium text-gris-chaud">3</span>
            <span data-libelle-etape class="hidden text-sm font-medium text-gris-chaud sm:inline">Confirmation</span>
        </div>
    </div>

    <form method="post" action="/gerant/commandes/creer" id="formulaire-nouvelle-commande">
        <input type="hidden" name="client_id" id="champ-client-id" value="">
        <input type="hidden" name="statut_initial" id="champ-statut-initial" value="PRETE">

        <!-- Étape 1 : recherche du client -->
        <div data-etape-contenu="1" class="rounded-xl border border-creme bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-charbon">Rechercher le client par téléphone</p>

            <div class="relative mt-3">
                <input
                    type="text"
                    id="recherche-telephone"
                    autocomplete="off"
                    placeholder="Numéro de téléphone..."
                    class="h-10 w-full rounded-md border border-creme bg-white px-3 text-sm text-charbon focus:border-bordeaux focus:outline-none focus:ring-3 focus:ring-bordeaux/10"
                >
                <div id="suggestions-clients" class="absolute left-0 right-0 top-full z-10 mt-1 hidden max-h-64 overflow-y-auto rounded-md border border-creme bg-white shadow-lg"></div>
            </div>

            <p id="message-recherche-client" class="mt-2 text-sm text-gris-chaud"></p>

            <div id="client-selectionne-carte" class="mt-4 hidden items-center justify-between rounded-md border border-bordeaux/30 bg-bordeaux/5 px-4 py-3">
                <div>
                    <p id="client-selectionne-nom" class="text-sm font-medium text-charbon"></p>
                    <p id="client-selectionne-telephone" class="text-xs text-gris-chaud"></p>
                </div>
                <button type="button" id="bouton-changer-client" class="text-xs font-medium text-bordeaux hover:underline">
                    Changer
                </button>
            </div>

            <div class="mt-5 flex justify-end">
                <button type="button" id="bouton-suivant-1" disabled
                    class="rounded-md bg-bordeaux px-5 py-2.5 text-sm font-medium text-ivoire transition-colors hover:bg-bordeaux-sombre disabled:cursor-not-allowed disabled:opacity-40">
                    Suivant
                </button>
            </div>
        </div>

        <!-- Étape 2 : sélection des produits -->
        <div data-etape-contenu="2" class="hidden rounded-xl border border-creme bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-charbon">Sélectionner les produits</p>

            <input
                type="text"
                id="recherche-produit-commande"
                placeholder="Rechercher un produit..."
                class="mt-3 h-10 w-full rounded-md border border-creme bg-white px-3 text-sm text-charbon focus:border-bordeaux focus:outline-none focus:ring-3 focus:ring-bordeaux/10"
            >

            <div class="mt-3 max-h-96 overflow-y-auto rounded-md border border-creme">
                <div class="divide-y divide-creme">
                    <?php foreach ($produits as $produit): ?>
                        <div
                            data-ligne-produit-commande
                            data-nom-produit="<?= View::e(mb_strtolower($produit->getLibelle())) ?>"
                            class="flex items-center justify-between gap-3 px-4 py-2.5"
                        >
                            <div class="flex items-center gap-3">
                                <img src="<?= View::e($produit->getImage() ?? '/assets/images/produit-defaut.svg') ?>" alt="" class="h-9 w-9 shrink-0 rounded-md object-cover">
                                <div>
                                    <p class="text-sm font-medium text-charbon"><?= View::e($produit->getLibelle()) ?></p>
                                    <p class="text-xs text-gris-chaud">
                                        <?= number_format($produit->getPrix(), 0, ',', ' ') ?> FCFA · <?= $produit->getQuantiteStock() ?> en stock
                                    </p>
                                </div>
                            </div>
                            <input
                                type="number"
                                name="quantite[<?= $produit->getId() ?>]"
                                data-quantite-produit
                                data-produit-id="<?= $produit->getId() ?>"
                                min="0"
                                max="<?= $produit->getQuantiteStock() ?>"
                                value="0"
                                class="h-9 w-20 rounded-md border border-creme bg-white px-2.5 text-sm text-charbon focus:border-bordeaux focus:outline-none focus:ring-3 focus:ring-bordeaux/10"
                            >
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="mt-4 flex items-center justify-between border-t border-creme pt-4">
                <p class="text-sm text-charbon">
                    <span id="compteur-articles">0</span> article(s) sélectionné(s)
                </p>
                <p class="font-voice text-lg font-medium text-bordeaux">
                    <span id="total-etape-2">0</span> FCFA
                </p>
            </div>

            <div class="mt-5 flex justify-between">
                <button type="button" id="bouton-precedent-2" class="rounded-md border border-creme px-5 py-2.5 text-sm font-medium text-charbon transition-colors hover:bg-ivoire">
                    Précédent
                </button>
                <button type="button" id="bouton-suivant-2" disabled
                    class="rounded-md bg-bordeaux px-5 py-2.5 text-sm font-medium text-ivoire transition-colors hover:bg-bordeaux-sombre disabled:cursor-not-allowed disabled:opacity-40">
                    Suivant
                </button>
            </div>
        </div>

        <!-- Étape 3 : confirmation -->
        <div data-etape-contenu="3" class="hidden rounded-xl border border-creme bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-charbon">Confirmer la commande</p>

            <div class="mt-3 rounded-md border border-creme bg-ivoire px-4 py-3">
                <p class="text-xs uppercase tracking-wide text-gris-chaud">Client</p>
                <p id="recap-client-nom" class="mt-0.5 text-sm font-medium text-charbon"></p>
            </div>

            <div class="mt-3 divide-y divide-creme rounded-md border border-creme" id="recap-lignes"></div>

            <div class="mt-4 flex items-center justify-between border-t border-creme pt-4">
                <p class="text-sm text-charbon">Total</p>
                <p class="font-voice text-xl font-medium text-bordeaux">
                    <span id="total-etape-3">0</span> FCFA
                </p>
            </div>

            <div class="mt-4">
                <label for="statut-initial-select" class="mb-1.5 block text-sm font-medium text-charbon">Statut initial</label>
                <select id="statut-initial-select"
                    class="h-10 w-full rounded-md border border-creme bg-white px-3 text-sm text-charbon focus:border-bordeaux focus:outline-none focus:ring-3 focus:ring-bordeaux/10 sm:w-64">
                    <option value="PRETE">Prête</option>
                    <option value="RETIREE">Retirée (déjà remise au client)</option>
                </select>
            </div>

            <div class="mt-5 flex justify-between">
                <button type="button" id="bouton-precedent-3" class="rounded-md border border-creme px-5 py-2.5 text-sm font-medium text-charbon transition-colors hover:bg-ivoire">
                    Précédent
                </button>
                <button type="submit" class="rounded-md bg-bordeaux px-5 py-2.5 text-sm font-medium text-ivoire transition-colors hover:bg-bordeaux-sombre">
                    Créer la commande
                </button>
            </div>
        </div>

    </form>

</div>

<script>
(function () {
    const produits = <?= $produitsJson ?>;
    const produitsParId = Object.fromEntries(produits.map((produit) => [produit.id, produit]));

    let clientChoisi = null;

    // --- Navigation entre étapes ---
    function afficherEtape(numero) {
        document.querySelectorAll('[data-etape-contenu]').forEach((bloc) => {
            bloc.classList.toggle('hidden', bloc.dataset.etapeContenu !== String(numero));
        });

        document.querySelectorAll('[data-etape-indicateur]').forEach((indicateur) => {
            const cercle = indicateur.querySelector('[data-cercle-etape]');
            const libelle = indicateur.querySelector('[data-libelle-etape]');
            const etapeNumero = Number(indicateur.dataset.etapeIndicateur);

            if (etapeNumero <= numero) {
                cercle.classList.remove('border-creme', 'bg-white', 'text-gris-chaud');
                cercle.classList.add('border-bordeaux', 'bg-bordeaux', 'text-ivoire');
                libelle.classList.remove('text-gris-chaud');
                libelle.classList.add('text-charbon');
            } else {
                cercle.classList.remove('border-bordeaux', 'bg-bordeaux', 'text-ivoire');
                cercle.classList.add('border-creme', 'bg-white', 'text-gris-chaud');
                libelle.classList.remove('text-charbon');
                libelle.classList.add('text-gris-chaud');
            }
        });
    }

    // --- Étape 1 : recherche client en direct ---
    const rechercheTelephone = document.getElementById('recherche-telephone');
    const suggestionsClients = document.getElementById('suggestions-clients');
    const messageRechercheClient = document.getElementById('message-recherche-client');
    const clientSelectionneCarte = document.getElementById('client-selectionne-carte');
    const clientSelectionneNom = document.getElementById('client-selectionne-nom');
    const clientSelectionneTelephone = document.getElementById('client-selectionne-telephone');
    const boutonChangerClient = document.getElementById('bouton-changer-client');
    const boutonSuivant1 = document.getElementById('bouton-suivant-1');
    const champClientId = document.getElementById('champ-client-id');

    let debounceRecherche = null;

    rechercheTelephone.addEventListener('input', () => {
        clearTimeout(debounceRecherche);
        const valeur = rechercheTelephone.value.trim();

        if (valeur === '') {
            suggestionsClients.classList.add('hidden');
            suggestionsClients.innerHTML = '';
            messageRechercheClient.textContent = '';
            return;
        }

        debounceRecherche = setTimeout(() => {
            fetch(`/gerant/commandes/clients-recherche?telephone=${encodeURIComponent(valeur)}`)
                .then((reponse) => reponse.json())
                .then((clients) => {
                    suggestionsClients.innerHTML = '';

                    if (clients.length === 0) {
                        messageRechercheClient.textContent = 'Aucun client trouvé avec ce numéro.';
                        suggestionsClients.classList.add('hidden');
                        return;
                    }

                    messageRechercheClient.textContent = '';

                    clients.forEach((client) => {
                        const item = document.createElement('button');
                        item.type = 'button';
                        item.className = 'flex w-full items-center justify-between px-4 py-2.5 text-left text-sm transition-colors hover:bg-ivoire';
                        item.innerHTML = `
                            <span class="font-medium text-charbon">${client.prenom} ${client.nom}</span>
                            <span class="text-gris-chaud">${client.telephone ?? '—'}</span>
                        `;
                        item.addEventListener('click', () => selectionnerClient(client));
                        suggestionsClients.appendChild(item);
                    });

                    suggestionsClients.classList.remove('hidden');
                })
                .catch(() => {
                    messageRechercheClient.textContent = 'Erreur lors de la recherche. Réessayez.';
                });
        }, 300);
    });

    function selectionnerClient(client) {
        clientChoisi = client;
        champClientId.value = client.id;

        clientSelectionneNom.textContent = `${client.prenom} ${client.nom}`;
        clientSelectionneTelephone.textContent = client.telephone ?? '—';
        clientSelectionneCarte.classList.remove('hidden');
        clientSelectionneCarte.classList.add('flex');

        rechercheTelephone.value = '';
        suggestionsClients.classList.add('hidden');
        suggestionsClients.innerHTML = '';
        messageRechercheClient.textContent = '';

        boutonSuivant1.disabled = false;
    }

    boutonChangerClient.addEventListener('click', () => {
        clientChoisi = null;
        champClientId.value = '';
        clientSelectionneCarte.classList.add('hidden');
        clientSelectionneCarte.classList.remove('flex');
        boutonSuivant1.disabled = true;
    });

    document.addEventListener('click', (evenement) => {
        if (!suggestionsClients.contains(evenement.target) && evenement.target !== rechercheTelephone) {
            suggestionsClients.classList.add('hidden');
        }
    });

    boutonSuivant1.addEventListener('click', () => afficherEtape(2));

    // --- Étape 2 : sélection produits ---
    const rechercheProduitCommande = document.getElementById('recherche-produit-commande');
    const compteurArticles = document.getElementById('compteur-articles');
    const totalEtape2 = document.getElementById('total-etape-2');
    const boutonSuivant2 = document.getElementById('bouton-suivant-2');
    const boutonPrecedent2 = document.getElementById('bouton-precedent-2');

    rechercheProduitCommande.addEventListener('input', () => {
        const motCle = rechercheProduitCommande.value.trim().toLowerCase();
        document.querySelectorAll('[data-ligne-produit-commande]').forEach((ligne) => {
            ligne.classList.toggle('hidden', !ligne.dataset.nomProduit.includes(motCle));
        });
    });

    function recalculerTotauxEtape2() {
        let nombreArticles = 0;
        let total = 0;

        document.querySelectorAll('[data-quantite-produit]').forEach((input) => {
            const quantite = parseInt(input.value, 10) || 0;
            if (quantite > 0) {
                nombreArticles += 1;
                const produit = produitsParId[input.dataset.produitId];
                total += quantite * produit.prix;
            }
        });

        compteurArticles.textContent = nombreArticles;
        totalEtape2.textContent = total.toLocaleString('fr-FR');
        boutonSuivant2.disabled = nombreArticles === 0;
    }

    document.querySelectorAll('[data-quantite-produit]').forEach((input) => {
        input.addEventListener('input', recalculerTotauxEtape2);
    });

    boutonPrecedent2.addEventListener('click', () => afficherEtape(1));
    boutonSuivant2.addEventListener('click', () => {
        construireRecapitulatif();
        afficherEtape(3);
    });

    // --- Étape 3 : confirmation ---
    const recapClientNom = document.getElementById('recap-client-nom');
    const recapLignes = document.getElementById('recap-lignes');
    const totalEtape3 = document.getElementById('total-etape-3');
    const statutInitialSelect = document.getElementById('statut-initial-select');
    const champStatutInitial = document.getElementById('champ-statut-initial');
    const boutonPrecedent3 = document.getElementById('bouton-precedent-3');

    function construireRecapitulatif() {
        recapClientNom.textContent = clientChoisi ? `${clientChoisi.prenom} ${clientChoisi.nom}` : '—';

        recapLignes.innerHTML = '';
        let total = 0;

        document.querySelectorAll('[data-quantite-produit]').forEach((input) => {
            const quantite = parseInt(input.value, 10) || 0;
            if (quantite <= 0) {
                return;
            }

            const produit = produitsParId[input.dataset.produitId];
            const sousTotal = quantite * produit.prix;
            total += sousTotal;

            const ligne = document.createElement('div');
            ligne.className = 'flex items-center justify-between px-4 py-2.5 text-sm';
            ligne.innerHTML = `
                <span class="text-charbon">${produit.libelle} <span class="text-gris-chaud">× ${quantite}</span></span>
                <span class="font-medium text-charbon">${sousTotal.toLocaleString('fr-FR')} FCFA</span>
            `;
            recapLignes.appendChild(ligne);
        });

        totalEtape3.textContent = total.toLocaleString('fr-FR');
    }

    statutInitialSelect.addEventListener('change', () => {
        champStatutInitial.value = statutInitialSelect.value;
    });

    boutonPrecedent3.addEventListener('click', () => afficherEtape(2));

    afficherEtape(1);
})();
</script>