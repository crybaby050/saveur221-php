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
 * Gère l'inscription des clients et la connexion partagée entre clients et
 * personnel interne (Gérant, Administrateur) : un seul formulaire, la
 * redirection après succès dépend simplement du type de compte trouvé.
 * L'inscription reste réservée aux clients : un compte interne n'est créé
 * que par un Administrateur déjà connecté, voir UtilisateurController.
 */
final class AuthController
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly ClientService $clientService,
    ) {
    }

    /**
     * Affiche le formulaire d'inscription (clients uniquement).
     */
    public function afficherInscription(): void
    {
        View::render('auth/inscription', layout: null);
    }

    /**
     * Traite la soumission du formulaire d'inscription.
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
            View::render('auth/inscription', ['erreur' => $exception->getMessage()], layout: null);
        }
    }

    /**
     * Affiche le formulaire de connexion, commun aux clients et au
     * personnel interne.
     */
    public function afficherConnexion(): void
    {
        View::render('auth/connexion', layout: null);
    }

    /**
     * Traite la soumission du formulaire de connexion, commun aux clients et
     * au personnel interne. Tente d'abord l'authentification client ; si
     * aucun client ne correspond à cet email, tente ensuite l'authentification
     * du personnel interne. Chaque tentative réussie écrit sa propre clé de
     * session (client_id ou utilisateur_id) via AuthService, jamais les deux
     * à la fois.
     */
    public function connecter(): void
    {
        $email = $_POST['email'] ?? '';
        $motDePasse = $_POST['mot_de_passe'] ?? '';
    
        try {
            $this->authService->authentifierClient($email, $motDePasse);
    
            Response::redirect('/produits');
            return;
        } catch (UtilisateurInexistantException) {
            // Pas de client avec cet email : on tente le personnel interne
            // ci-dessous, plutôt que d'échouer immédiatement.
        } catch (MotDePasseIncorrectException $exception) {
            View::render('auth/connexion', ['erreur' => $exception->getMessage()], layout: null);
            return;
        }
    
        try {
            $this->authService->authentifierUtilisateur($email, $motDePasse);
    
            Response::redirect('/gerant/dashboard');
        } catch (UtilisateurInexistantException|MotDePasseIncorrectException|CompteDesactiveException $exception) {
            View::render('auth/connexion', ['erreur' => 'Email ou mot de passe incorrect.'], layout: null);
        }
    }

    /**
     * Déconnecte l'acteur actuellement en session (client ou utilisateur
     * interne), quel qu'il soit.
     */
    public function deconnecter(): void
    {
        $this->authService->deconnecter();

        Response::redirect('/connexion');
    }
}