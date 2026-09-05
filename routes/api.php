<?php

declare(strict_types=1);

/*
 * Routes JSON, destinées aux actions ponctuelles en AJAX pour fluidifier
 * l'expérience utilisateur sans recharger la page. Volontairement
 * minimal : seules les actions du panier bénéficient réellement d'un
 * aller-retour asynchrone ; le reste du site reste en rendu serveur
 * classique via web.php.
 */

use App\Controllers\Api\PanierApiController;

/** @var \Core\Router $router */

$router->get('/api/panier', [PanierApiController::class, 'contenu']);
$router->post('/api/panier/ajouter', [PanierApiController::class, 'ajouter']);
$router->post('/api/panier/modifier', [PanierApiController::class, 'modifierQuantite']);
$router->post('/api/panier/retirer/{produitId}', [PanierApiController::class, 'retirer']);