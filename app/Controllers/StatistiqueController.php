<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\AuthService;
use App\Services\CommandeService;
use Core\View;

/*
 * Affiche le tableau de bord statistique (US "Consulter le tableau de
 * bord statistique"). Statistiques::calculerStatistiques() sera ajoutée
 * à CommandeService PHP sur le même principe qu'en Java, une fois cette
 * méthode nécessaire — pour l'instant ce contrôleur appelle une méthode
 * qui reste à écrire.
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

        View::render('gerant/statistiques/index', [
            'statistiques' => $this->commandeService->calculerStatistiques(),
        ]);
    }
}