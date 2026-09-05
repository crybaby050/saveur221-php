<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\CompteDesactiveException;
use App\Exceptions\EmailDejaUtiliseException;
use App\Exceptions\MotDePasseIncorrectException;
use App\Exceptions\MotDePasseInvalideException;
use App\Exceptions\UtilisateurInexistantException;
use App\Services\AuthService;
use App\Services\ClientService;
use Core\Response;
use Core\View;

/*
 * Gère l'inscription et la connexion des clients (espace public du site).
 * La connexion des utilisateurs internes (Gérant, Administrateur) suit un
 * flux séparé, voir AuthInterneController — deux populations d'acteurs
 * distinctes, avec des règles de validation différentes (actif n'existe
 * pas pour un client).
 */
final class AuthController
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly ClientService $clientService,
    ) {
    }

    /**
     * Affiche le formulaire d'inscription.
     */
    public function afficherInscription(): void
    {
        View::render('auth/inscription');
    }

    /**
     * Traite la soumission du formulaire d'inscription. Redirige vers la
     * page de connexion en cas de succès, ou réaffiche le formulaire avec
     * un message d'erreur en cas d'échec — pattern Post/Redirect/Get
     * appliqué uniquement au cas de succès, puisqu'une erreur doit
     * permettre au client de corriger sa saisie sans tout retaper.
     */
    public function inscrire(): void
    {
        try {
            $this->clientService->inscrire(
                nom: $_POST['nom'] ?? '',
                prenom: $_POST['prenom'] ?? '',
                email: $_POST['email'] ?? '',
                motDePasse: $_POST['mot_de_passe'] ?? '',
                telephone: $_POST['telephone'] ?: null,
                adresse: $_POST['adresse'] ?: null,
            );

            Response::redirect('/connexion');
        } catch (EmailDejaUtiliseException|MotDePasseInvalideException $exception) {
            View::render('auth/inscription', ['erreur' => $exception->getMessage()]);
        }
    }

    /**
     * Affiche le formulaire de connexion.
     */
    public function afficherConnexion(): void
    {
        View::render('auth/connexion');
    }

    /**
     * Traite la soumission du formulaire de connexion. Redirige vers le
     * catalogue en cas de succès.
     */
    public function connecter(): void
    {
        try {
            $this->authService->authentifierClient(
                email: $_POST['email'] ?? '',
                motDePasse: $_POST['mot_de_passe'] ?? '',
            );

            Response::redirect('/produits');
        } catch (UtilisateurInexistantException|MotDePasseIncorrectException $exception) {
            View::render('auth/connexion', ['erreur' => $exception->getMessage()]);
        }
    }

    /**
     * Déconnecte le client actuellement en session.
     */
    public function deconnecter(): void
    {
        $this->authService->deconnecter();

        Response::redirect('/connexion');
    }
}