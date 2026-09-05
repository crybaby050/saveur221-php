<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\CompteDesactiveException;
use App\Exceptions\MotDePasseIncorrectException;
use App\Exceptions\UtilisateurInexistantException;
use App\Services\AuthService;
use Core\Response;
use Core\View;

/*
 * Gère la connexion des utilisateurs internes (Gérant, Administrateur).
 * Contrairement à AuthController, il n'y a pas d'inscription possible ici
 * : un compte interne n'est créé que par un Administrateur déjà connecté,
 * voir UtilisateurController.
 */
final class AuthInterneController
{
    public function __construct(
        private readonly AuthService $authService,
    ) {
    }

    /**
     * Affiche le formulaire de connexion réservé au personnel interne.
     */
    public function afficherConnexion(): void
    {
        View::render('auth/connexion-interne');
    }

    /**
     * Traite la soumission du formulaire de connexion interne. Redirige
     * vers le tableau de bord en cas de succès.
     */
    public function connecter(): void
    {
        try {
            $this->authService->authentifierUtilisateur(
                email: $_POST['email'] ?? '',
                motDePasse: $_POST['mot_de_passe'] ?? '',
            );

            Response::redirect('/gerant/dashboard');
        } catch (UtilisateurInexistantException|MotDePasseIncorrectException|CompteDesactiveException $exception) {
            View::render('auth/connexion-interne', ['erreur' => $exception->getMessage()]);
        }
    }

    /**
     * Déconnecte l'utilisateur interne actuellement en session.
     */
    public function deconnecter(): void
    {
        $this->authService->deconnecter();

        Response::redirect('/interne/connexion');
    }
}