<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\AuthService;
use App\Services\AvisService;
use App\Services\ClientService;
use App\Services\ProduitService;
use Core\Paginateur;
use Core\Response;

final class AvisInterneController extends ControllerInterneBase
{
    public function __construct(
        private readonly AvisService $avisService,
        private readonly ProduitService $produitService,
        private readonly ClientService $clientService,
        AuthService $authService,
    ) {
        parent::__construct($authService);
    }

    public function index(): void
    {
        $this->exigerAdministrateur();

        $note = isset($_GET['note']) && $_GET['note'] !== '' ? (int) $_GET['note'] : null;
        $avis = $note !== null
            ? $this->avisService->filtrerParNote($note)
            : $this->avisService->listerAvis();

        $pagination = new Paginateur($avis, (int) ($_GET['page'] ?? 1));

        $this->afficherVueInterne('admin/avis/index', [
            'avisEnrichis' => $this->enrichirAvis($pagination->elements),
            'pagination' => $pagination,
            'noteActive' => $note,
        ]);
    }

    public function supprimer(string $id): void
    {
        $this->exigerAdministrateur();

        $this->avisService->supprimerAvis((int) $id);

        Response::redirect('/admin/avis');
    }

    /**
     * Associe à chaque avis le nom du produit concerné et celui du client
     * l'ayant déposé, pour un affichage lisible côté modération. Un avis
     * dont le produit ou le client aurait été supprimé depuis reste
     * affichable, avec un libellé de repli.
     *
     * @param \App\Models\Avis[] $avisListe Avis à enrichir
     * @return array<array{avis: \App\Models\Avis, produitNom: string, clientNom: string}>
     */
    private function enrichirAvis(array $avisListe): array
    {
        return array_map(function ($avis) {
            $produit = $this->produitService->consulterProduit($avis->getProduitId());
            $client = $this->clientService->consulterClient($avis->getClientId());

            return [
                'avis' => $avis,
                'produitNom' => $produit?->getLibelle() ?? 'Produit supprimé',
                'clientNom' => $client ? trim($client->getPrenom() . ' ' . $client->getNom()) : 'Client supprimé',
            ];
        }, $avisListe);
    }
}