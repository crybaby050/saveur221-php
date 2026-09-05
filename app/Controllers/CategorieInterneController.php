<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\CategorieUtiliseeException;
use App\Services\AuthService;
use App\Services\CategorieService;
use Core\Response;
use Core\View;

/*
 * Gère les catégories depuis l'espace interne (Gérant/Admin). Nommé
 * CategorieInterneController pour le distinguer d'un futur contrôleur
 * public si le catalogue avait besoin d'une route de consultation dédiée
 * aux catégories seules.
 */
final class CategorieInterneController extends ControllerInterneBase
{
    public function __construct(
        private readonly CategorieService $categorieService,
        AuthService $authService,
    ) {
        parent::__construct($authService);
    }

    /**
     * Affiche la liste des catégories, avec recherche optionnelle.
     */
    public function index(): void
    {
        $this->exigerUtilisateurConnecte();

        $motCle = $_GET['recherche'] ?? null;
        $categories = $motCle !== null
            ? $this->categorieService->rechercherCategorie($motCle)
            : $this->categorieService->listerCategories();

        View::render('gerant/categories/index', ['categories' => $categories]);
    }

    /**
     * Affiche le formulaire d'ajout d'une catégorie.
     */
    public function afficherAjout(): void
    {
        $this->exigerUtilisateurConnecte();

        View::render('gerant/categories/ajouter');
    }

    /**
     * Traite la soumission du formulaire d'ajout. couleur et image sont
     * facultatifs : le service se charge d'attribuer une valeur par
     * défaut si le Gérant ne les précise pas.
     */
    public function ajouter(): void
    {
        $this->exigerUtilisateurConnecte();

        $this->categorieService->ajouterCategorie(
            nom: $_POST['nom'] ?? '',
            description: $_POST['description'] ?: null,
            image: $_POST['image'] ?: null,
            couleur: $_POST['couleur'] ?: null,
        );

        Response::redirect('/gerant/categories');
    }

    /**
     * Affiche le formulaire de modification d'une catégorie existante.
     *
     * @param string $id Identifiant de la catégorie, extrait de l'URL par le Router
     */
    public function afficherModification(string $id): void
    {
        $this->exigerUtilisateurConnecte();

        $categorie = $this->categorieService->consulterCategorie((int) $id);

        View::render('gerant/categories/modifier', ['categorie' => $categorie]);
    }

    /**
     * Traite la soumission du formulaire de modification.
     *
     * @param string $id Identifiant de la catégorie, extrait de l'URL par le Router
     */
    public function modifier(string $id): void
    {
        $this->exigerUtilisateurConnecte();

        $this->categorieService->modifierCategorie(
            id: (int) $id,
            nom: $_POST['nom'] ?? '',
            description: $_POST['description'] ?: null,
            image: $_POST['image'] ?: null,
            couleur: $_POST['couleur'] ?: null,
        );

        Response::redirect('/gerant/categories');
    }

    /**
     * Supprime une catégorie. Redirige avec un message d'erreur si elle
     * contient encore des produits, plutôt que de faire échouer la
     * requête brutalement.
     *
     * @param string $id Identifiant de la catégorie, extrait de l'URL par le Router
     */
    public function supprimer(string $id): void
    {
        $this->exigerUtilisateurConnecte();

        try {
            $this->categorieService->supprimerCategorie((int) $id);

            Response::redirect('/gerant/categories');
        } catch (CategorieUtiliseeException $exception) {
            View::render('gerant/categories/index', [
                'categories' => $this->categorieService->listerCategories(),
                'erreur' => $exception->getMessage(),
            ]);
        }
    }
}