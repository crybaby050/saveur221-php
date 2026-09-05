<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\CommandeInexistanteException;
use App\Exceptions\MontantPaiementInvalideException;
use App\Services\AuthService;
use App\Services\PaiementService;
use App\Services\RecuService;
use Core\Response;
use Core\View;

/*
 * Gère l'enregistrement des paiements et la consultation des commandes
 * impayées depuis l'espace interne (Gérant/Admin).
 */
final class PaiementInterneController extends ControllerInterneBase
{
    public function __construct(
        private readonly PaiementService $paiementService,
        private readonly RecuService $recuService,
        AuthService $authService,
    ) {
        parent::__construct($authService);
    }

    /**
     * Affiche les commandes impayées ou partiellement payées.
     */
    public function commandesImpayees(): void
    {
        $this->exigerUtilisateurConnecte();

        View::render('gerant/paiements/impayees', [
            'commandes' => $this->paiementService->consulterCommandesImpayees(),
        ]);
    }

    /**
     * Affiche l'historique des paiements et des reçus d'une commande.
     *
     * @param string $commandeId Identifiant de la commande, extrait de l'URL par le Router
     */
    public function historique(string $commandeId): void
    {
        $this->exigerUtilisateurConnecte();

        View::render('gerant/paiements/historique', [
            'paiements' => $this->paiementService->consulterParCommande((int) $commandeId),
            'recus' => $this->recuService->consulterParCommande((int) $commandeId),
        ]);
    }

    /**
     * Traite l'enregistrement d'un nouveau paiement pour une commande.
     *
     * @param string $commandeId Identifiant de la commande, extrait de l'URL par le Router
     */
    public function enregistrer(string $commandeId): void
    {
        $this->exigerUtilisateurConnecte();

        try {
            $this->paiementService->enregistrerPaiement(
                (int) $commandeId,
                (float) ($_POST['montant'] ?? 0)
            );

            Response::redirect("/gerant/paiements/{$commandeId}");
        } catch (CommandeInexistanteException|MontantPaiementInvalideException $exception) {
            View::render('gerant/paiements/historique', [
                'paiements' => $this->paiementService->consulterParCommande((int) $commandeId),
                'recus' => $this->recuService->consulterParCommande((int) $commandeId),
                'erreur' => $exception->getMessage(),
            ]);
        }
    }
}