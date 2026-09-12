<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\AuthService;
use Core\Response;

/*
 * Ne gère plus que la déconnexion du personnel interne : la connexion
 * est désormais unifiée avec celle des clients, voir AuthController. Ce
 * contrôleur reste séparé car le formulaire de déconnexion de l'espace
 * interne (layout/interne.layout) poste vers /interne/deconnexion, et
 * redirige spécifiquement vers /connexion après coup.
 */
final class AuthInterneController
{
    public function __construct(
        private readonly AuthService $authService,
    ) {
    }

    public function deconnecter(): void
    {
        $this->authService->deconnecter();

        Response::redirect('/connexion');
    }
}