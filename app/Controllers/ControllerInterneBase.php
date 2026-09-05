<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Enums\Role;
use App\Exceptions\AccesRefuseException;
use App\Models\Utilisateur;
use App\Services\AuthService;
use Core\Response;

/*
 * Base commune à tous les contrôleurs de l'espace interne (Gérant,
 * Administrateur). Centralise la vérification de connexion, ainsi que la
 * vérification de rôle pour les actions réservées à l'Administrateur
 * (US "L'espace admin est réservé à ADMIN").
 */
abstract class ControllerInterneBase
{
    public function __construct(
        protected readonly AuthService $authService,
    ) {
    }

    /**
     * Interrompt la requête et redirige vers la connexion interne si aucun
     * utilisateur n'est authentifié, sinon retourne l'utilisateur connecté.
     * Utilisée par toute action accessible au Gérant comme à
     * l'Administrateur (l'Administrateur possédant tous les droits du
     * Gérant).
     *
     * @return Utilisateur L'utilisateur interne actuellement connecté
     */
    protected function exigerUtilisateurConnecte(): Utilisateur
    {
        $utilisateur = $this->authService->utilisateurConnecte();

        if ($utilisateur === null) {
            Response::redirect('/interne/connexion');
        }

        return $utilisateur;
    }

    /**
     * Comme exigerUtilisateurConnecte(), mais lève en plus une exception
     * si l'utilisateur connecté n'a pas le rôle ADMIN — utilisée par les
     * actions réservées à l'Administrateur (gestion des utilisateurs
     * internes, des clients, des avis).
     *
     * @return Utilisateur L'administrateur actuellement connecté
     *
     * @throws AccesRefuseException si l'utilisateur connecté n'est pas Administrateur
     */
    protected function exigerAdministrateur(): Utilisateur
    {
        $utilisateur = $this->exigerUtilisateurConnecte();

        if ($utilisateur->getRole() !== Role::ADMIN) {
            throw new AccesRefuseException("L'espace administrateur est réservé au rôle ADMIN.");
        }

        return $utilisateur;
    }
}