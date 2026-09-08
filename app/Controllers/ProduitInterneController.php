<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\AuthService;
use App\Services\CategorieService;
use App\Services\ProduitService;
use Core\Response;

final class ProduitInterneController extends ControllerInterneBase
{
    public function __construct(
        private readonly ProduitService $produitService,
        private readonly CategorieService $categorieService,
        AuthService $authService,
    ) {
        parent::__construct($authService);
    }

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

        $this->afficherVueInterne('gerant/produits/index', [
            'produits' => $produits,
            'categories' => $this->categorieService->listerCategories(),
        ]);
    }

    public function stock(): void
    {
        $this->exigerUtilisateurConnecte();

        $this->afficherVueInterne('gerant/produits/stock', [
            'produits' => $this->produitService->listerProduits(),
            'stockFaible' => $this->produitService->consulterStockFaible(),
            'ruptures' => $this->produitService->consulterRuptures(),
        ]);
    }

    public function afficherAjout(): void
    {
        $this->exigerUtilisateurConnecte();

        $this->afficherVueInterne('gerant/produits/ajouter', [
            'categories' => $this->categorieService->listerCategories(),
        ]);
    }

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

    public function afficherModification(string $id): void
    {
        $this->exigerUtilisateurConnecte();

        $this->afficherVueInterne('gerant/produits/modifier', [
            'produit' => $this->produitService->consulterProduit((int) $id),
            'categories' => $this->categorieService->listerCategories(),
        ]);
    }

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

    public function supprimer(string $id): void
    {
        $this->exigerUtilisateurConnecte();

        $this->produitService->supprimerProduit((int) $id);

        Response::redirect('/gerant/produits');
    }

    public function approvisionner(string $id): void
    {
        $this->exigerUtilisateurConnecte();

        $this->produitService->approvisionner((int) $id, (int) ($_POST['quantite'] ?? 0));

        Response::redirect('/gerant/produits/stock');
    }

    public function definirSeuilAlerte(string $id): void
    {
        $this->exigerUtilisateurConnecte();

        $this->produitService->definirSeuilAlerte((int) $id, (int) ($_POST['seuil'] ?? 0));

        Response::redirect('/gerant/produits/stock');
    }
}