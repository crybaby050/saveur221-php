<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\AuthService;
use App\Services\ClientService;
use App\Services\CommandeService;
use Core\View;

/*
 * Gère la consultation des clients depuis l'espace Administrateur.
 * Entièrement en lecture seule : la création et la modification d'un
 * compte client relèvent exclusivement du client lui-même, via l'espace
 * public (ProfilController, AuthController).
 */
final class ClientInterneController extends ControllerInterneBase
{
    public function __construct(
        private readonly ClientService $clientService,
        private readonly CommandeService $commandeService,
        AuthService $authService,
    ) {
        parent::__construct($authService);
    }

    /**
     * Affiche la liste des clients, avec recherche optionnelle.
     */
    public function index(): void
    {
        $this->exigerAdministrateur();

        $motCle = $_GET['recherche'] ?? null;
        $clients = $motCle !== null
            ? $this->clientService->rechercherClient($motCle)
            : $this->clientService->listerClients();

        View::render('admin/clients/index', ['clients' => $clients]);
    }

    /**
     * Affiche le détail d'un client, avec l'historique de ses commandes.
     *
     * @param string $id Identifiant du client, extrait de l'URL par le Router
     */
    public function detail(string $id): void
    {
        $this->exigerAdministrateur();

        View::render('admin/clients/detail', [
            'client' => $this->clientService->consulterClient((int) $id),
            'commandes' => $this->commandeService->listerCommandesClient((int) $id),
        ]);
    }
}