<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\AuthService;
use App\Services\FactureService;
use Core\View;

/*
 * Gère la consultation des factures depuis l'espace interne (Gérant/Admin).
 * Entièrement en lecture seule : une facture est générée automatiquement
 * par CommandeService, jamais créée ni modifiée manuellement.
 */
final class FactureInterneController extends ControllerInterneBase
{
    public function __construct(
        private readonly FactureService $factureService,
        AuthService $authService,
    ) {
        parent::__construct($authService);
    }

    /**
     * Affiche la liste de toutes les factures émises.
     */
    public function index(): void
    {
        $this->exigerUtilisateurConnecte();

        View::render('gerant/factures/index', ['factures' => $this->factureService->listerFactures()]);
    }

    /**
     * Recherche une facture par son numéro lisible.
     */
    public function rechercher(): void
    {
        $this->exigerUtilisateurConnecte();

        $facture = $this->factureService->rechercherParNumero($_GET['numero'] ?? '');

        View::render('gerant/factures/index', [
            'factures' => $this->factureService->listerFactures(),
            'factureTrouvee' => $facture,
        ]);
    }
}