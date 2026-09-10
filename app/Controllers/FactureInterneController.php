<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\AuthService;
use App\Services\FactureService;
use Core\View;

final class FactureInterneController extends ControllerInterneBase
{
    public function __construct(
        private readonly FactureService $factureService,
        AuthService $authService,
    ) {
        parent::__construct($authService);
    }

    /**
     * Affiche la liste des factures, avec recherche optionnelle par numéro.
     */
    public function index(): void
    {
        $numero = $_GET['recherche'] ?? null;

        $factureTrouvee = ($numero !== null && $numero !== '')
            ? $this->factureService->rechercherParNumero($numero)
            : null;

        View::render('gerant/factures/index', $this->avecUtilisateur([
            'titrePage' => 'Factures',
            'section' => 'factures',
            'factures' => $this->factureService->listerFactures(),
            'motCle' => $numero,
            'factureTrouvee' => $factureTrouvee,
            'aucunResultat' => $numero !== null && $numero !== '' && $factureTrouvee === null,
        ]), 'layout/interne.layout');
    }
}