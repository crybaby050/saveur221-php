<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\AuthService;
use App\Services\AvisService;
use Core\Response;
use Core\View;

/*
 * Gère la modération des avis depuis l'espace Administrateur : consultation,
 * filtrage par note, suppression d'un avis inapproprié. Le dépôt d'un avis
 * relève exclusivement du client, voir AvisController.
 */
final class AvisInterneController extends ControllerInterneBase
{
    public function __construct(
        private readonly AvisService $avisService,
        AuthService $authService,
    ) {
        parent::__construct($authService);
    }

    /**
     * Affiche la liste des avis, avec filtrage par note optionnel.
     */
    public function index(): void
    {
        $this->exigerAdministrateur();

        $note = isset($_GET['note']) ? (int) $_GET['note'] : null;
        $avis = $note !== null
            ? $this->avisService->filtrerParNote($note)
            : $this->avisService->listerAvis();

        View::render('admin/avis/index', ['avis' => $avis]);
    }

    /**
     * Supprime un avis jugé inapproprié.
     *
     * @param string $id Identifiant de l'avis, extrait de l'URL par le Router
     */
    public function supprimer(string $id): void
    {
        $this->exigerAdministrateur();

        $this->avisService->supprimerAvis((int) $id);

        Response::redirect('/admin/avis');
    }
}