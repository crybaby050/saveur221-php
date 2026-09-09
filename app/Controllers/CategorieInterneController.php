<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\CategorieUtiliseeException;
use App\Services\AuthService;
use App\Services\CategorieService;
use Core\Response;

final class CategorieInterneController extends ControllerInterneBase
{
    public function __construct(
        private readonly CategorieService $categorieService,
        AuthService $authService,
    ) {
        parent::__construct($authService);
    }

    public function index(): void
    {
        $this->exigerUtilisateurConnecte();
    
        $motCle = $_GET['recherche'] ?? null;
        $categories = $motCle !== null && $motCle !== ''
            ? $this->categorieService->rechercherCategorie($motCle)
            : $this->categorieService->listerCategories();
    
        $pagination = new \Core\Paginateur($categories, (int) ($_GET['page'] ?? 1));
    
        $this->afficherVueInterne('gerant/categories/index', [
            'categories' => $pagination->elements,
            'pagination' => $pagination,
            'motCle' => $motCle,
        ]);
    }

    public function afficherAjout(): void
    {
        $this->exigerUtilisateurConnecte();

        $this->afficherVueInterne('gerant/categories/ajouter');
    }

    public function ajouter(): void
    {
        $this->exigerUtilisateurConnecte();

        $image = $this->uploaderImageSiPresente('categories');

        $this->categorieService->ajouterCategorie(
            nom: $_POST['nom'] ?? '',
            description: $_POST['description'] ?: null,
            image: $image,
            couleur: $_POST['couleur'] ?: null,
        );

        Response::redirect('/gerant/categories');
    }

    public function afficherModification(string $id): void
    {
        $this->exigerUtilisateurConnecte();

        $categorie = $this->categorieService->consulterCategorie((int) $id);

        $this->afficherVueInterne('gerant/categories/modifier', ['categorie' => $categorie]);
    }

    public function modifier(string $id): void
    {
        $this->exigerUtilisateurConnecte();

        $image = $this->uploaderImageSiPresente('categories');

        $this->categorieService->modifierCategorie(
            id: (int) $id,
            nom: $_POST['nom'] ?? '',
            description: $_POST['description'] ?: null,
            image: $image,
            couleur: $_POST['couleur'] ?: null,
        );

        Response::redirect('/gerant/categories');
    }

    public function supprimer(string $id): void
    {
        $this->exigerUtilisateurConnecte();

        try {
            $this->categorieService->supprimerCategorie((int) $id);

            Response::redirect('/gerant/categories');
        } catch (CategorieUtiliseeException $exception) {
            $this->afficherVueInterne('gerant/categories/index', [
                'categories' => $this->categorieService->listerCategories(),
                'erreur' => $exception->getMessage(),
            ]);
        }
    }

    private function uploaderImageSiPresente(string $dossier): ?string
    {
        if (empty($_FILES['image']['name'])) {
            return null;
        }

        return \Core\CloudinaryService::uploader($_FILES['image'], $dossier);
    }
}