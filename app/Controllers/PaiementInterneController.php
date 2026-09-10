<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\CommandeInexistanteException;
use App\Exceptions\MontantPaiementInvalideException;
use App\Services\AuthService;
use App\Services\PaiementService;
use App\Services\RecuService;
use Core\Response;

final class PaiementInterneController extends ControllerInterneBase
{
    public function __construct(
        private readonly PaiementService $paiementService,
        private readonly RecuService $recuService,
        AuthService $authService,
    ) {
        parent::__construct($authService);
    }

    public function commandesImpayees(): void
    {
        $this->exigerUtilisateurConnecte();

        $this->afficherVueInterne('gerant/paiements/impayees', [
            'titrePage' => 'Tous les paiements',
            'section' => 'paiements',
            'commandes' => $this->paiementService->consulterToutesCommandesAvecPaiements(),
        ]);
    }

    public function historique(string $commandeId): void
    {
        $this->exigerUtilisateurConnecte();

        $commande = $this->paiementService->consulterCommande((int) $commandeId);

        if ($commande === null) {
            Response::redirect('/gerant/paiements/impayees');
            return;
        }

        $this->afficherVueInterne('gerant/paiements/historique', [
            'titrePage' => 'Paiements de la commande',
            'section' => 'paiements',
            'commandeId' => (int) $commandeId,
            'commande' => $commande,
            'paiements' => $this->paiementService->consulterParCommande((int) $commandeId),
            'recus' => $this->recuService->consulterParCommande((int) $commandeId),
        ]);
    }

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
            $commande = $this->paiementService->consulterCommande((int) $commandeId);

            if ($commande === null) {
                Response::redirect('/gerant/paiements/impayees');
                return;
            }

            $this->afficherVueInterne('gerant/paiements/historique', [
                'titrePage' => 'Paiements de la commande',
                'section' => 'paiements',
                'commandeId' => (int) $commandeId,
                'commande' => $commande,
                'paiements' => $this->paiementService->consulterParCommande((int) $commandeId),
                'recus' => $this->recuService->consulterParCommande((int) $commandeId),
                'erreur' => $exception->getMessage(),
            ]);
        }
    }
}