<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\AvisDejaDeposeException;
use App\Exceptions\AvisNonAutoriseException;
use App\Services\AuthService;
use App\Services\AvisService;
use Core\Response;

/*
 * Traite le dépôt d'un avis par le client, sur un produit qu'il a
 * effectivement commandé et reçu. Le formulaire lui-même est intégré
 * directement sous la fiche produit (voir produits/detail.php), donc ce
 * contrôleur n'a plus qu'à traiter la soumission.
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
            Response::redirect("/produits/{$produitId}?erreur=" . urlencode($exception->getMessage()));
        }
    }
}