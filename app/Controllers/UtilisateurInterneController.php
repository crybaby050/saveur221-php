<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Enums\Role;
use App\Exceptions\EmailDejaUtiliseException;
use App\Exceptions\MotDePasseInvalideException;
use App\Services\AuthService;
use App\Services\UtilisateurService;
use Core\Paginateur;
use Core\Response;

final class UtilisateurInterneController extends ControllerInterneBase
{
    public function __construct(
        private readonly UtilisateurService $utilisateurService,
        AuthService $authService,
    ) {
        parent::__construct($authService);
    }

    public function index(): void
    {
        $this->exigerAdministrateur();

        $motCle = $_GET['recherche'] ?? null;
        $utilisateurs = $motCle !== null && $motCle !== ''
            ? $this->utilisateurService->rechercherUtilisateur($motCle)
            : $this->utilisateurService->listerUtilisateurs();

        $pagination = new Paginateur($utilisateurs, (int) ($_GET['page'] ?? 1));

        $this->afficherVueInterne('admin/utilisateurs/index', [
            'utilisateurs' => $pagination->elements,
            'pagination' => $pagination,
            'motCle' => $motCle,
        ]);
    }

    public function afficherAjout(): void
    {
        $this->exigerAdministrateur();

        $this->afficherVueInterne('admin/utilisateurs/ajouter');
    }

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
            $pagination = new Paginateur($this->utilisateurService->listerUtilisateurs(), 1);

            $this->afficherVueInterne('admin/utilisateurs/index', [
                'utilisateurs' => $pagination->elements,
                'pagination' => $pagination,
                'motCle' => null,
                'erreur' => $exception->getMessage(),
            ]);
        }
    }

    public function afficherModification(string $id): void
    {
        $this->exigerAdministrateur();

        $this->afficherVueInterne('admin/utilisateurs/modifier', [
            'utilisateur' => $this->utilisateurService->consulterUtilisateur((int) $id),
        ]);
    }

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

    public function supprimer(string $id): void
    {
        $this->exigerAdministrateur();

        $this->utilisateurService->supprimerUtilisateur((int) $id);

        Response::redirect('/admin/utilisateurs');
    }

    public function activer(string $id): void
    {
        $this->exigerAdministrateur();

        $this->utilisateurService->activer((int) $id);

        Response::redirect('/admin/utilisateurs');
    }

    public function desactiver(string $id): void
    {
        $this->exigerAdministrateur();

        $this->utilisateurService->desactiver((int) $id);

        Response::redirect('/admin/utilisateurs');
    }

    public function changerRole(string $id): void
    {
        $admin = $this->exigerAdministrateur();

        try {
            $this->utilisateurService->changerRole(
                (int) $id,
                Role::from($_POST['role'] ?? Role::GERANT->value),
                $admin->getId(),
            );

            Response::redirect('/admin/utilisateurs');
        } catch (\App\Exceptions\ModificationCompteProprieException $exception) {
            $pagination = new Paginateur($this->utilisateurService->listerUtilisateurs(), 1);

            $this->afficherVueInterne('admin/utilisateurs/index', [
                'utilisateurs' => $pagination->elements,
                'pagination' => $pagination,
                'motCle' => null,
                'erreur' => $exception->getMessage(),
            ]);
        }
    }
}