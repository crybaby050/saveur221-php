<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\AuthService;
use App\Services\CategorieService;
use App\Services\ProduitService;
use Core\Response;
use Core\View;

/*
 * Gère les produits et le stock depuis l'espace interne (Gérant/Admin).
 */
final class ProduitInterneController extends ControllerInterneBase
{
    public function __construct(
        private readonly ProduitService $produitService,
        private readonly CategorieService $categorieService,
        AuthService $authService,
    ) {
        parent::__construct($authService);
    }

    /**
     * Affiche la liste des produits, avec recherche et filtrage par
     * catégorie optionnels.
     */
    public function index(): void
    {
        $this->exigerUtilisateurConnecte();

        $motCle = $_GET['recherche'] ?? null;
        $categorieId = isset($_GET['categorie']) ? (int) $_GET['categorie'] : null;

        $produits = match (true) {
            $motCle !== null => $this->produitService->rechercherProduit($motCle),
            $categorieId !== null => $this->produitService->filtrerParCategorie($categorieId),
            default => $this->produitService->listerProduits(),
        };

        View::render('gerant/produits/index', [
            'produits' => $produits,
            'categories' => $this->categorieService->listerCategories(),
        ]);
    }

    /**
     * Affiche l'état global du stock : tous les produits, ceux en stock
     * faible, et ceux en rupture.
     */
    public function stock(): void
    {
        $this->exigerUtilisateurConnecte();

        View::render('gerant/produits/stock', [
            'produits' => $this->produitService->listerProduits(),
            'stockFaible' => $this->produitService->consulterStockFaible(),
            'ruptures' => $this->produitService->consulterRuptures(),
        ]);
    }

    /**
     * Affiche le formulaire d'ajout d'un produit.
     */
    public function afficherAjout(): void
    {
        $this->exigerUtilisateurConnecte();

        View::render('gerant/produits/ajouter', ['categories' => $this->categorieService->listerCategories()]);
    }

    /**
     * Traite la soumission du formulaire d'ajout.
     */
    public function ajouter(): void
    {
        $this->exigerUtilisateurConnecte();

        $this->produitService->ajouterProduit(
            libelle: $_POST['libelle'] ?? '',
            description: $_POST['description'] ?: null,
            prix: (float) ($_POST['prix'] ?? 0),
            quantiteStock: (int) ($_POST['quantite_stock'] ?? 0),
            seuilAlerte: (int) ($_POST['seuil_alerte'] ?? 5),
            categorieId: (int) ($_POST['categorie_id'] ?? 0),
            image: $_POST['image'] ?: null,
        );

        Response::redirect('/gerant/produits');
    }

    /**
     * Affiche le formulaire de modification d'un produit existant.
     *
     * @param string $id Identifiant du produit, extrait de l'URL par le Router
     */
    public function afficherModification(string $id): void
    {
        $this->exigerUtilisateurConnecte();

        View::render('gerant/produits/modifier', [
            'produit' => $this->produitService->consulterProduit((int) $id),
            'categories' => $this->categorieService->listerCategories(),
        ]);
    }

    /**
     * Traite la soumission du formulaire de modification. N'affecte
     * jamais le stock : voir approvisionner() pour cette opération.
     *
     * @param string $id Identifiant du produit, extrait de l'URL par le Router
     */
    public function modifier(string $id): void
    {
        $this->exigerUtilisateurConnecte();

        $this->produitService->modifierProduit(
            id: (int) $id,
            libelle: $_POST['libelle'] ?? '',
            description: $_POST['description'] ?: null,
            prix: (float) ($_POST['prix'] ?? 0),
            categorieId: (int) ($_POST['categorie_id'] ?? 0),
            image: $_POST['image'] ?: null,
        );

        Response::redirect('/gerant/produits');
    }

    /**
     * Supprime un produit.
     *
     * @param string $id Identifiant du produit, extrait de l'URL par le Router
     */
    public function supprimer(string $id): void
    {
        $this->exigerUtilisateurConnecte();

        $this->produitService->supprimerProduit((int) $id);

        Response::redirect('/gerant/produits');
    }

    /**
     * Traite la soumission du formulaire d'approvisionnement d'un produit.
     *
     * @param string $id Identifiant du produit, extrait de l'URL par le Router
     */
    public function approvisionner(string $id): void
    {
        $this->exigerUtilisateurConnecte();

        $this->produitService->approvisionner((int) $id, (int) ($_POST['quantite'] ?? 0));

        Response::redirect('/gerant/produits/stock');
    }

    /**
     * Traite la soumission du formulaire de définition du seuil d'alerte.
     *
     * @param string $id Identifiant du produit, extrait de l'URL par le Router
     */
    public function definirSeuilAlerte(string $id): void
    {
        $this->exigerUtilisateurConnecte();

        $this->produitService->definirSeuilAlerte((int) $id, (int) ($_POST['seuil'] ?? 0));

        Response::redirect('/gerant/produits/stock');
    }
}