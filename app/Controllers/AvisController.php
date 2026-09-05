<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\AccesRefuseException;
use App\Exceptions\AvisDejaDeposeException;
use App\Exceptions\AvisNonAutoriseException;
use App\Exceptions\CommandeInexistanteException;
use App\Services\AuthService;
use App\Services\AvisService;
use Core\Response;
use Core\View;

/*
 * Gère le dépôt d'un avis par le client, sur une commande qu'il a
 * effectivement passée et qui a été retirée.
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
     * Affiche le formulaire de dépôt d'avis pour une commande.
     *
     * @param string $commandeId Identifiant de la commande, extrait de l'URL par le Router
     */
    public function afficherFormulaire(string $commandeId): void
    {
        $this->exigerClientConnecte();

        View::render('avis/formulaire', ['commandeId' => (int) $commandeId]);
    }

    /**
     * Traite la soumission du formulaire de dépôt d'avis.
     *
     * @param string $commandeId Identifiant de la commande, extrait de l'URL par le Router
     */
    public function deposer(string $commandeId): void
    {
        $client = $this->exigerClientConnecte();

        try {
            $this->avisService->deposerAvis(
                clientId: $client->getId(),
                commandeId: (int) $commandeId,
                note: (int) ($_POST['note'] ?? 0),
                commentaire: $_POST['commentaire'] ?: null,
            );

            Response::redirect("/commandes/{$commandeId}");
        } catch (CommandeInexistanteException|AccesRefuseException|AvisNonAutoriseException|AvisDejaDeposeException $exception) {
            View::render('avis/formulaire', [
                'commandeId' => (int) $commandeId,
                'erreur' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * Interrompt la requête et redirige vers la connexion si aucun client
     * n'est actuellement authentifié, sinon retourne le client connecté.
     *
     * @return \App\Models\Client Le client actuellement connecté
     */
    private function exigerClientConnecte(): \App\Models\Client
    {
        $client = $this->authService->clientConnecte();

        if ($client === null) {
            Response::redirect('/connexion');
        }

        return $client;
    }
}