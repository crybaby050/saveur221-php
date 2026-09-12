<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Commande;
use App\Models\Facture;
use App\Repositories\FactureRepository;
use DateTimeImmutable;

/*
 * Génère et consulte les factures. Une facture est toujours créée
 * automatiquement par CommandeService juste après la création d'une
 * commande — jamais appelée directement depuis un contrôleur pour créer
 * une facture "à la main".
 */
final class FactureService
{
    public function __construct(
        private readonly FactureRepository $factureRepository,
    ) {
    }

    /**
     * Génère la facture correspondant à une commande, avec un numéro
     * lisible unique. Le montant est figé à la valeur de la commande au
     * moment de l'appel, pour ne jamais varier même si la commande était
     * recalculée ultérieurement.
     *
     * @param Commande $commande Commande à facturer
     * @return Facture La facture créée
     */
    public function genererFacture(Commande $commande): Facture
    {
        $numero = $this->genererNumeroFacture();

        $facture = new Facture(0, $numero, $commande->getId(), $commande->getMontantTotal(), new DateTimeImmutable());

        return $this->factureRepository->creer($facture);
    }

    /**
     * Retourne toutes les factures émises.
     *
     * @return Facture[] Liste de toutes les factures
     */
    public function listerFactures(): array
    {
        return $this->factureRepository->trouverTous();
    }

    /**
     * Récupère la facture associée à une commande donnée.
     *
     * @param int $commandeId Identifiant de la commande recherchée
     * @return Facture|null La facture trouvée, ou null si la commande n'en a pas
     */
    public function consulterParCommande(int $commandeId): ?Facture
    {
        return $this->factureRepository->trouverParCommande($commandeId);
    }

    /**
     * Recherche une facture par son numéro lisible.
     *
     * @param string $numeroFacture Numéro de facture recherché
     * @return Facture|null La facture trouvée, ou null si aucune ne correspond
     */
    public function rechercherParNumero(string $numeroFacture): ?Facture
    {
        return $this->factureRepository->trouverParNumero($numeroFacture);
    }

    /**
     * Génère un numéro de facture lisible du type FAC-2026-000104, basé
     * sur le nombre de factures déjà émises. Approche volontairement
     * simple, cohérente avec celle du Java Console — à revoir avec une
     * séquence dédiée si le volume de commandes simultanées venait à
     * poser un risque de collision.
     *
     * @return string Numéro de facture généré
     */
    private function genererNumeroFacture(): string
    {
        $nombreFactures = count($this->factureRepository->trouverTous());
        $annee = (new DateTimeImmutable())->format('Y');

        return sprintf('FAC-%s-%06d', $annee, $nombreFactures + 1);
    }
}