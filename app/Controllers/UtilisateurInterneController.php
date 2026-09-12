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
    
        $motDePasse = $_POST['mot_de_passe'] ?? '';
        $confirmation = $_POST['mot_de_passe_confirmation'] ?? '';
    
        if ($motDePasse !== $confirmation) {
            $this->redirigerAvecErreur('La confirmation du mot de passe ne correspond pas.');
            return;
        }
    
        try {
            $this->utilisateurService->ajouterUtilisateur(
                nom: $_POST['nom'] ?? '',
                prenom: $_POST['prenom'] ?? '',
                email: $_POST['email'] ?? '',
                motDePasse: $motDePasse,
                role: Role::from($_POST['role'] ?? Role::GERANT->value),
            );
    
            Response::redirect('/admin/utilisateurs');
        } catch (EmailDejaUtiliseException|MotDePasseInvalideException $exception) {
            $this->redirigerAvecErreur($exception->getMessage());
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
        $admin = $this->exigerAdministrateur();

        try {
            $this->utilisateurService->supprimerUtilisateur((int) $id, $admin->getId());
            Response::redirect('/admin/utilisateurs');
        } catch (ModificationCompteProprieException $exception) {
            $this->redirigerAvecErreur($exception->getMessage());
        }
    }

    public function activer(string $id): void
    {
        $this->exigerAdministrateur();

        $this->utilisateurService->activer((int) $id);

        Response::redirect('/admin/utilisateurs');
    }

    public function desactiver(string $id): void
    {
        $admin = $this->exigerAdministrateur();

        try {
            $this->utilisateurService->desactiver((int) $id, $admin->getId());
            Response::redirect('/admin/utilisateurs');
        } catch (ModificationCompteProprieException $exception) {
            $this->redirigerAvecErreur($exception->getMessage());
        }
    }

    /**
     * Réaffiche la liste des utilisateurs avec un message d'erreur — factorisé
     * puisque changerRole(), desactiver() et supprimer() partagent tous les
     * trois ce même besoin en cas de tentative d'auto-modification.
     */
    private function redirigerAvecErreur(string $message): void
    {
        $pagination = new Paginateur($this->utilisateurService->listerUtilisateurs(), 1);

        $this->afficherVueInterne('admin/utilisateurs/index', [
            'utilisateurs' => $pagination->elements,
            'pagination' => $pagination,
            'motCle' => null,
            'erreur' => $message,
        ]);
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