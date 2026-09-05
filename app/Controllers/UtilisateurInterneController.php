<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Enums\Role;
use App\Exceptions\EmailDejaUtiliseException;
use App\Exceptions\MotDePasseInvalideException;
use App\Services\AuthService;
use App\Services\UtilisateurService;
use Core\Response;
use Core\View;

/*
 * Gère les utilisateurs internes (Gérant, Administrateur) — action
 * exclusivement réservée à l'Administrateur, d'où l'appel systématique à
 * exigerAdministrateur() plutôt qu'exigerUtilisateurConnecte().
 */
final class UtilisateurInterneController extends ControllerInterneBase
{
    public function __construct(
        private readonly UtilisateurService $utilisateurService,
        AuthService $authService,
    ) {
        parent::__construct($authService);
    }

    /**
     * Affiche la liste des utilisateurs internes, avec recherche
     * optionnelle.
     */
    public function index(): void
    {
        $this->exigerAdministrateur();

        $motCle = $_GET['recherche'] ?? null;
        $utilisateurs = $motCle !== null
            ? $this->utilisateurService->rechercherUtilisateur($motCle)
            : $this->utilisateurService->listerUtilisateurs();

        View::render('admin/utilisateurs/index', ['utilisateurs' => $utilisateurs]);
    }

    /**
     * Affiche le formulaire d'ajout d'un utilisateur interne.
     */
    public function afficherAjout(): void
    {
        $this->exigerAdministrateur();

        View::render('admin/utilisateurs/ajouter');
    }

    /**
     * Traite la soumission du formulaire d'ajout.
     */
    public function ajouter(): void
    {
        $this->exigerAdministrateur();

        try {
            $this->utilisateurService->ajouterUtilisateur(
                nom: $_POST['nom'] ?? '',
                prenom: $_POST['prenom'] ?? '',
                email: $_POST['email'] ?? '',
                motDePasse: $_POST['mot_de_passe'] ?? '',
                role: Role::from($_POST['role'] ?? Role::GERANT->value),
            );

            Response::redirect('/admin/utilisateurs');
        } catch (EmailDejaUtiliseException|MotDePasseInvalideException $exception) {
            View::render('admin/utilisateurs/ajouter', ['erreur' => $exception->getMessage()]);
        }
    }

    /**
     * Affiche le formulaire de modification d'un utilisateur existant.
     *
     * @param string $id Identifiant de l'utilisateur, extrait de l'URL par le Router
     */
    public function afficherModification(string $id): void
    {
        $this->exigerAdministrateur();

        View::render('admin/utilisateurs/modifier', [
            'utilisateur' => $this->utilisateurService->consulterUtilisateur((int) $id),
        ]);
    }

    /**
     * Traite la soumission du formulaire de modification. Ne touche
     * jamais au mot de passe, au statut d'activation ni au rôle : voir
     * les actions dédiées pour ces opérations.
     *
     * @param string $id Identifiant de l'utilisateur, extrait de l'URL par le Router
     */
    public function modifier(string $id): void
    {
        $this->exigerAdministrateur();

        $this->utilisateurService->modifierUtilisateur(
            id: (int) $id,
            nom: $_POST['nom'] ?? '',
            prenom: $_POST['prenom'] ?? '',
            email: $_POST['email'] ?? '',
        );

        Response::redirect('/admin/utilisateurs');
    }

    /**
     * Supprime un utilisateur interne.
     *
     * @param string $id Identifiant de l'utilisateur, extrait de l'URL par le Router
     */
    public function supprimer(string $id): void
    {
        $this->exigerAdministrateur();

        $this->utilisateurService->supprimerUtilisateur((int) $id);

        Response::redirect('/admin/utilisateurs');
    }

    /**
     * Réactive un compte utilisateur interne.
     *
     * @param string $id Identifiant de l'utilisateur, extrait de l'URL par le Router
     */
    public function activer(string $id): void
    {
        $this->exigerAdministrateur();

        $this->utilisateurService->activer((int) $id);

        Response::redirect('/admin/utilisateurs');
    }

    /**
     * Désactive un compte utilisateur interne.
     *
     * @param string $id Identifiant de l'utilisateur, extrait de l'URL par le Router
     */
    public function desactiver(string $id): void
    {
        $this->exigerAdministrateur();

        $this->utilisateurService->desactiver((int) $id);

        Response::redirect('/admin/utilisateurs');
    }

    /**
     * Traite le changement de rôle d'un utilisateur interne.
     *
     * @param string $id Identifiant de l'utilisateur, extrait de l'URL par le Router
     */
    public function changerRole(string $id): void
    {
        $this->exigerAdministrateur();

        $this->utilisateurService->changerRole((int) $id, Role::from($_POST['role'] ?? Role::GERANT->value));

        Response::redirect('/admin/utilisateurs');
    }
}