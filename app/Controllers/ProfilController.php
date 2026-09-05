<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\MotDePasseInvalideException;
use App\Models\Client;
use App\Services\AuthService;
use App\Services\ClientService;
use Core\Response;
use Core\View;

/*
 * Gère la consultation et la modification du profil du client connecté
 * (US "Modifier son profil").
 */
final class ProfilController extends ControllerClientBase
{
    public function __construct(
        private readonly ClientService $clientService,
        AuthService $authService,
    ) {
        parent::__construct($authService);
    }

    /**
     * Affiche le formulaire de profil, pré-rempli avec les informations
     * actuelles du client connecté.
     */
    public function afficher(): void
    {
        $client = $this->exigerClientConnecte();

        View::render('profil/index', ['client' => $client]);
    }

    /**
     * Traite la soumission du formulaire de modification de profil.
     */
    public function modifier(): void
    {
        $client = $this->exigerClientConnecte();

        $this->clientService->modifierProfil(
            id: $client->getId(),
            nom: $_POST['nom'] ?? $client->getNom(),
            prenom: $_POST['prenom'] ?? $client->getPrenom(),
            email: $_POST['email'] ?? $client->getEmail(),
            telephone: $_POST['telephone'] ?: null,
            adresse: $_POST['adresse'] ?: null,
        );

        Response::redirect('/profil');
    }

    /**
     * Traite la soumission du formulaire de changement de mot de passe,
     * séparé du formulaire de profil général.
     */
    public function changerMotDePasse(): void
    {
        $client = $this->exigerClientConnecte();

        try {
            $this->clientService->changerMotDePasse($client->getId(), $_POST['mot_de_passe'] ?? '');

            Response::redirect('/profil');
        } catch (MotDePasseInvalideException $exception) {
            View::render('profil/index', ['client' => $client, 'erreur' => $exception->getMessage()]);
        }
    }

    /**
     * Interrompt la requête et redirige vers la connexion si aucun client
     * n'est actuellement authentifié, sinon retourne le client connecté.
     *
     * @return Client Le client actuellement connecté
     */
    private function exigerClientConnecte(): Client
    {
        $client = $this->authService->clientConnecte();

        if ($client === null) {
            Response::redirect('/connexion');
        }

        return $client;
    }
}