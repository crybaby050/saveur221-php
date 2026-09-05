<?php

declare(strict_types=1);

/*
 * Routes HTML de l'espace interne (Gérant, Administrateur). Séparées de
 * web.php pour garder une frontière claire entre l'espace public/client
 * et l'espace personnel, même si les deux fichiers alimentent le même
 * Router — la distinction d'accès (rôle requis) est de toute façon gérée
 * par chaque contrôleur via ControllerInterneBase, pas par le routeur.
 */

use App\Controllers\AuthInterneController;
use App\Controllers\AvisInterneController;
use App\Controllers\CategorieInterneController;
use App\Controllers\ClientInterneController;
use App\Controllers\CommandeInterneController;
use App\Controllers\FactureInterneController;
use App\Controllers\PaiementInterneController;
use App\Controllers\ProduitInterneController;
use App\Controllers\StatistiqueController;
use App\Controllers\UtilisateurInterneController;

/** @var \Core\Router $router */

/* Authentification interne */
$router->get('/interne/connexion', [AuthInterneController::class, 'afficherConnexion']);
$router->post('/interne/connexion', [AuthInterneController::class, 'connecter']);
$router->post('/interne/deconnexion', [AuthInterneController::class, 'deconnecter']);

/* Tableau de bord */
$router->get('/gerant/dashboard', [StatistiqueController::class, 'index']);

/* Catégories (Gérant, Administrateur) */
$router->get('/gerant/categories', [CategorieInterneController::class, 'index']);
$router->get('/gerant/categories/ajouter', [CategorieInterneController::class, 'afficherAjout']);
$router->post('/gerant/categories/ajouter', [CategorieInterneController::class, 'ajouter']);
$router->get('/gerant/categories/{id}/modifier', [CategorieInterneController::class, 'afficherModification']);
$router->post('/gerant/categories/{id}/modifier', [CategorieInterneController::class, 'modifier']);
$router->post('/gerant/categories/{id}/supprimer', [CategorieInterneController::class, 'supprimer']);

/* Produits et stock (Gérant, Administrateur) */
$router->get('/gerant/produits', [ProduitInterneController::class, 'index']);
$router->get('/gerant/produits/stock', [ProduitInterneController::class, 'stock']);
$router->get('/gerant/produits/ajouter', [ProduitInterneController::class, 'afficherAjout']);
$router->post('/gerant/produits/ajouter', [ProduitInterneController::class, 'ajouter']);
$router->get('/gerant/produits/{id}/modifier', [ProduitInterneController::class, 'afficherModification']);
$router->post('/gerant/produits/{id}/modifier', [ProduitInterneController::class, 'modifier']);
$router->post('/gerant/produits/{id}/supprimer', [ProduitInterneController::class, 'supprimer']);
$router->post('/gerant/produits/{id}/approvisionner', [ProduitInterneController::class, 'approvisionner']);
$router->post('/gerant/produits/{id}/seuil-alerte', [ProduitInterneController::class, 'definirSeuilAlerte']);

/* Commandes (Gérant, Administrateur) */
$router->get('/gerant/commandes', [CommandeInterneController::class, 'index']);
$router->get('/gerant/commandes/rechercher', [CommandeInterneController::class, 'rechercher']);
$router->get('/gerant/commandes/{id}', [CommandeInterneController::class, 'detail']);
$router->post('/gerant/commandes/{id}/statut', [CommandeInterneController::class, 'changerStatut']);
$router->post('/gerant/commandes/{id}/annuler', [CommandeInterneController::class, 'annuler']);

/* Paiements (Gérant, Administrateur) */
$router->get('/gerant/paiements/impayees', [PaiementInterneController::class, 'commandesImpayees']);
$router->get('/gerant/paiements/{commandeId}', [PaiementInterneController::class, 'historique']);
$router->post('/gerant/paiements/{commandeId}', [PaiementInterneController::class, 'enregistrer']);

/* Factures (Gérant, Administrateur) */
$router->get('/gerant/factures', [FactureInterneController::class, 'index']);
$router->get('/gerant/factures/rechercher', [FactureInterneController::class, 'rechercher']);

/* Utilisateurs internes (Administrateur uniquement) */
$router->get('/admin/utilisateurs', [UtilisateurInterneController::class, 'index']);
$router->get('/admin/utilisateurs/ajouter', [UtilisateurInterneController::class, 'afficherAjout']);
$router->post('/admin/utilisateurs/ajouter', [UtilisateurInterneController::class, 'ajouter']);
$router->get('/admin/utilisateurs/{id}/modifier', [UtilisateurInterneController::class, 'afficherModification']);
$router->post('/admin/utilisateurs/{id}/modifier', [UtilisateurInterneController::class, 'modifier']);
$router->post('/admin/utilisateurs/{id}/supprimer', [UtilisateurInterneController::class, 'supprimer']);
$router->post('/admin/utilisateurs/{id}/activer', [UtilisateurInterneController::class, 'activer']);
$router->post('/admin/utilisateurs/{id}/desactiver', [UtilisateurInterneController::class, 'desactiver']);
$router->post('/admin/utilisateurs/{id}/role', [UtilisateurInterneController::class, 'changerRole']);

/* Clients, consultation seule (Administrateur uniquement) */
$router->get('/admin/clients', [ClientInterneController::class, 'index']);
$router->get('/admin/clients/{id}', [ClientInterneController::class, 'detail']);

/* Avis, modération (Administrateur uniquement) */
$router->get('/admin/avis', [AvisInterneController::class, 'index']);
$router->post('/admin/avis/{id}/supprimer', [AvisInterneController::class, 'supprimer']);