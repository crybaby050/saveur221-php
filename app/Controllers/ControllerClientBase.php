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
}