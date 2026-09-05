<?php

declare(strict_types=1);

/*
 * Routes HTML de l'espace public et de l'espace Client. Chaque route
 * associe une méthode HTTP et un chemin à un contrôleur et une action.
 * $router est injecté depuis public/index.php, déjà construit avec le
 * Container courant.
 */

use App\Controllers\AuthController;
use App\Controllers\AvisController;
use App\Controllers\CommandeClientController;
use App\Controllers\PanierController;
use App\Controllers\ProduitController;
use App\Controllers\ProfilController;

/** @var \Core\Router $router */

/* Espace public : catalogue accessible sans authentification */
$router->get('/', [ProduitController::class, 'index']);
$router->get('/produits', [ProduitController::class, 'index']);
$router->get('/produits/{id}', [ProduitController::class, 'show']);

/* Authentification client */
$router->get('/inscription', [AuthController::class, 'afficherInscription']);
$router->post('/inscription', [AuthController::class, 'inscrire']);
$router->get('/connexion', [AuthController::class, 'afficherConnexion']);
$router->post('/connexion', [AuthController::class, 'connecter']);
$router->post('/deconnexion', [AuthController::class, 'deconnecter']);

/* Panier (réservé aux clients connectés, vérifié dans chaque contrôleur) */
$router->get('/panier', [PanierController::class, 'afficher']);
$router->post('/panier/ajouter', [PanierController::class, 'ajouter']);
$router->post('/panier/modifier', [PanierController::class, 'modifierQuantite']);
$router->post('/panier/retirer/{produitId}', [PanierController::class, 'retirer']);
$router->post('/panier/vider', [PanierController::class, 'vider']);

/* Commandes côté client */
$router->post('/commande/valider', [CommandeClientController::class, 'valider']);
$router->get('/commandes/historique', [CommandeClientController::class, 'historique']);
$router->get('/commandes/{id}/suivi', [CommandeClientController::class, 'suivi']);
$router->get('/commandes/{id}', [CommandeClientController::class, 'detail']);

/* Profil client */
$router->get('/profil', [ProfilController::class, 'afficher']);
$router->post('/profil', [ProfilController::class, 'modifier']);
$router->post('/profil/mot-de-passe', [ProfilController::class, 'changerMotDePasse']);

/* Avis, déposé sur une commande retirée */
$router->get('/commandes/{commandeId}/avis', [AvisController::class, 'afficherFormulaire']);
$router->post('/commandes/{commandeId}/avis', [AvisController::class, 'deposer']);