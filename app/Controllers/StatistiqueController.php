<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\AuthService;
use App\Services\CommandeService;

/*
 * Affiche le tableau de bord statistique (US "Consulter le tableau de
 * bord statistique").
 */
final class StatistiqueController extends ControllerInterneBase
{
    public function __construct(
        private readonly CommandeService $commandeService,
        AuthService $authService,
    ) {
        parent::__construct($authService);
    }

    /**
     * Affiche le tableau de bord.
     */
    public function index(): void
    {
        $this->exigerUtilisateurConnecte();

        $this->afficherVueInterne('gerant/statistiques/index', [
            'statistiques' => $this->commandeService->calculerStatistiques(),
        ]);
    }
}