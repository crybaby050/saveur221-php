<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Enums\StatutCommande;
use App\Exceptions\CommandeInexistanteException;
use App\Exceptions\TransitionStatutInvalideException;
use App\Services\AuthService;
use App\Services\CommandeService;
use Core\Response;
use Core\View;

/*
 * Gère les commandes depuis l'espace interne (Gérant/Admin) : consultation,
 * recherche, filtrage, changement de statut, annulation. Le passage de
 * commande côté client relève de CommandeClientController, aux règles
 * différentes.
 */
final class CommandeInterneController extends ControllerInterneBase
{
    public function __construct(
        private readonly CommandeService $commandeService,
        AuthService $authService,
    ) {
        parent::__construct($authService);
    }

    /**
     * Affiche la liste des commandes, avec filtrage par statut optionnel.
     */
    public function index(): void
    {
        $this->exigerUtilisateurConnecte();

        $statutParam = $_GET['statut'] ?? null;
        $commandes = $statutParam !== null
            ? $this->commandeService->filtrerParStatut(StatutCommande::from($statutParam))
            : $this->commandeService->listerCommandes();

        View::render('gerant/commandes/index', ['commandes' => $commandes]);
    }

    /**
     * Recherche une commande par son numéro lisible.
     */
    public function rechercher(): void
    {
        $this->exigerUtilisateurConnecte();

        try {
            $commande = $this->commandeService->rechercherParNumero($_GET['numero'] ?? '');

            View::render('gerant/commandes/detail', ['commande' => $commande]);
        } catch (CommandeInexistanteException $exception) {
            View::render('gerant/commandes/index', [
                'commandes' => $this->commandeService->listerCommandes(),
                'erreur' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * Affiche le détail d'une commande, avec ses lignes.
     *
     * @param string $id Identifiant de la commande, extrait de l'URL par le Router
     */
    public function detail(string $id): void
    {
        $this->exigerUtilisateurConnecte();

        View::render('gerant/commandes/detail', [
            'commande' => $this->commandeService->consulterCommande((int) $id),
        ]);
    }

    /**
     * Traite le changement de statut d'une commande.
     *
     * @param string $id Identifiant de la commande, extrait de l'URL par le Router
     */
    public function changerStatut(string $id): void
    {
        $this->exigerUtilisateurConnecte();

        try {
            $this->commandeService->changerStatut(
                (int) $id,
                StatutCommande::from($_POST['statut'] ?? '')
            );

            Response::redirect("/gerant/commandes/{$id}");
        } catch (TransitionStatutInvalideException $exception) {
            View::render('gerant/commandes/detail', [
                'commande' => $this->commandeService->consulterCommande((int) $id),
                'erreur' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * Annule une commande, quel que soit son statut courant. Le stock des
     * produits concernés est restitué automatiquement par le service.
     *
     * @param string $id Identifiant de la commande, extrait de l'URL par le Router
     */
    public function annuler(string $id): void
    {
        $this->exigerUtilisateurConnecte();

        $this->commandeService->changerStatut((int) $id, StatutCommande::ANNULEE);

        Response::redirect("/gerant/commandes/{$id}");
    }
}