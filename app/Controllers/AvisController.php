<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\AvisDejaDeposeException;
use App\Exceptions\AvisNonAutoriseException;
use App\Services\AuthService;
use App\Services\AvisService;
use Core\Response;
use Core\View;

/*
 * Gère le dépôt d'un avis par le client, sur un produit qu'il a
 * effectivement commandé et reçu.
 */
final class AvisController extends ControllerClientBase
{
    public function __construct(
        private readonly AvisService $avisService,
        AuthService $authService,
    ) {
        parent::__construct($authService);
    }

    /**
     * Affiche le formulaire de dépôt d'avis pour un produit.
     *
     * @param string $produitId Identifiant du produit, extrait de l'URL par le Router
     */
    public function afficherFormulaire(string $produitId): void
    {
        $client = $this->exigerClientConnecte();

        if (!$this->avisService->peutDeposerAvis($client->getId(), (int) $produitId)) {
            Response::redirect('/produits/' . $produitId);
            return;
        }

        View::render('avis/formulaire', ['produitId' => (int) $produitId], layout: 'layout/base.layout');
    }

    /**
     * Traite la soumission du formulaire de dépôt d'avis.
     *
     * @param string $produitId Identifiant du produit, extrait de l'URL par le Router
     */
    public function deposer(string $produitId): void
    {
        $client = $this->exigerClientConnecte();

        try {
            $this->avisService->deposerAvis(
                clientId: $client->getId(),
                produitId: (int) $produitId,
                note: (int) ($_POST['note'] ?? 0),
                commentaire: $_POST['commentaire'] ?: null,
            );

            Response::redirect("/produits/{$produitId}");
        } catch (AvisNonAutoriseException|AvisDejaDeposeException $exception) {
            View::render('avis/formulaire', [
                'produitId' => (int) $produitId,
                'erreur' => $exception->getMessage(),
            ], layout: 'layout/base.layout');
        }
    }
}