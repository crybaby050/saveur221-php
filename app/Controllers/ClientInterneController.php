<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\AuthService;
use App\Services\ClientService;
use App\Services\CommandeService;
use Core\Paginateur;

final class ClientInterneController extends ControllerInterneBase
{
    public function __construct(
        private readonly ClientService $clientService,
        private readonly CommandeService $commandeService,
        AuthService $authService,
    ) {
        parent::__construct($authService);
    }

    public function index(): void
    {
        $this->exigerAdministrateur();

        $motCle = $_GET['recherche'] ?? null;
        $clients = $motCle !== null && $motCle !== ''
            ? $this->clientService->rechercherClient($motCle)
            : $this->clientService->listerClients();

        $pagination = new Paginateur($clients, (int) ($_GET['page'] ?? 1));

        $this->afficherVueInterne('admin/clients/index', [
            'clients' => $pagination->elements,
            'pagination' => $pagination,
            'motCle' => $motCle,
        ]);
    }

    public function detail(string $id): void
    {
        $this->exigerAdministrateur();

        $this->afficherVueInterne('admin/clients/detail', [
            'client' => $this->clientService->consulterClient((int) $id),
            'commandes' => $this->commandeService->listerCommandesClient((int) $id),
        ]);
    }
}