<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Client;
use App\Services\AuthService;
use Core\Response;

/*
 * Base commune à tous les contrôleurs de l'espace Client, qui doivent
 * tous vérifier qu'un client est bien authentifié avant d'exécuter la
 * moindre action. Centralise cette vérification ici plutôt que de la
 * dupliquer dans chaque contrôleur — même logique de factorisation que
 * MenuView côté Java Console, appliquée cette fois à un contrôle d'accès
 * plutôt qu'à une boucle de menu.
 */
abstract class ControllerClientBase
{
    public function __construct(
        protected readonly AuthService $authService,
    ) {
    }

    /**
     * Interrompt la requête et redirige vers la page de connexion si
     * aucun client n'est actuellement authentifié, sinon retourne le
     * client connecté. Les sous-classes appellent cette méthode en tout
     * début de chaque action nécessitant une authentification.
     *
     * @return Client Le client actuellement connecté
     */
    protected function exigerClientConnecte(): Client
    {
        $client = $this->authService->clientConnecte();

        if ($client === null) {
            Response::redirect('/connexion');
        }

        return $client;
    }

    /**
     * Fusionne les données communes à toutes les vues client (le client
     * connecté, ou null pour un visiteur) avec les données propres à la vue
     * appelante — évite d'oublier de transmettre $client au layout public
     * dans chaque contrôleur.
     *
     * @param array $donnees Données spécifiques à la vue
     * @return array Données complètes à transmettre à View::render()
     */
    protected function avecClient(array $donnees): array
    {
        return [...$donnees, 'client' => $this->authService->clientConnecte()];
    }

    
}