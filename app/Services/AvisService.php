<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\AvisDejaDeposeException;
use App\Exceptions\AvisNonAutoriseException;
use App\Models\Avis;
use App\Repositories\AvisRepository;
use DateTimeImmutable;

/*
 * Applique les règles métier liées aux avis clients, désormais rattachés
 * à un produit plutôt qu'à une commande précise : dépôt (réservé au
 * client ayant effectivement commandé et reçu ce produit), et modération
 * côté administrateur.
 */
final class AvisService
{
    private const NOTE_MIN = 1;
    private const NOTE_MAX = 5;

    public function __construct(
        private readonly AvisRepository $avisRepository,
    ) {
    }

    /**
     * Retourne tous les avis déposés, utilisée pour la modération côté
     * administrateur.
     *
     * @return Avis[] Liste de tous les avis
     */
    public function listerAvis(): array
    {
        return $this->avisRepository->trouverTous();
    }

    /**
     * Retourne les avis ayant une note exacte donnée.
     *
     * @param int $note Note recherchée (entre 1 et 5)
     * @return Avis[] Avis correspondant à cette note
     */
    public function filtrerParNote(int $note): array
    {
        return $this->avisRepository->trouverParNote($note);
    }

    /**
     * Retourne les avis déposés sur un produit, avec sa note moyenne —
     * utilisée pour l'affichage sur la fiche produit du catalogue.
     *
     * @param int $produitId Identifiant du produit recherché
     * @return array{avis: Avis[], noteMoyenne: float|null} Avis du produit et sa note moyenne
     */
    public function consulterParProduit(int $produitId): array
    {
        return [
            'avis' => $this->avisRepository->trouverParProduit($produitId),
            'noteMoyenne' => $this->avisRepository->noteMoyenne($produitId),
        ];
    }

    /**
     * Indique si un client peut déposer un avis sur un produit donné : il
     * doit l'avoir commandé dans une commande RETIREE, et ne pas lui avoir
     * déjà attribué d'avis. Utilisée par le contrôleur pour n'afficher le
     * bouton "Donner un avis" que sur les produits réellement éligibles.
     *
     * @param int $clientId  Identifiant du client concerné
     * @param int $produitId Identifiant du produit concerné
     * @return bool true si le client peut déposer un avis sur ce produit
     */
    public function peutDeposerAvis(int $clientId, int $produitId): bool
    {
        return $this->avisRepository->aCommandeEtRetireProduit($clientId, $produitId)
            && !$this->avisRepository->existeParClientEtProduit($clientId, $produitId);
    }

    /**
     * Dépose un avis sur un produit, après vérification des règles
     * métier : le client doit avoir commandé ce produit dans une commande
     * RETIREE, et ne doit pas lui avoir déjà attribué d'avis auparavant.
     *
     * @param int         $clientId    Identifiant du client déposant l'avis
     * @param int         $produitId   Identifiant du produit concerné
     * @param int         $note        Note attribuée, doit être comprise entre 1 et 5
     * @param string|null $commentaire Commentaire facultatif
     * @return Avis L'avis créé
     *
     * @throws AvisNonAutoriseException si le client n'a jamais reçu ce produit
     * @throws AvisDejaDeposeException si un avis existe déjà pour ce produit
     * @throws \InvalidArgumentException si la note est hors de la plage 1 à 5
     */
    public function deposerAvis(int $clientId, int $produitId, int $note, ?string $commentaire): Avis
    {
        if ($note < self::NOTE_MIN || $note > self::NOTE_MAX) {
            throw new \InvalidArgumentException('La note doit être comprise entre 1 et 5.');
        }

        if (!$this->avisRepository->aCommandeEtRetireProduit($clientId, $produitId)) {
            throw new AvisNonAutoriseException(
                'Un avis ne peut être déposé que sur un produit déjà commandé et retiré.'
            );
        }

        if ($this->avisRepository->existeParClientEtProduit($clientId, $produitId)) {
            throw new AvisDejaDeposeException('Vous avez déjà déposé un avis pour ce produit.');
        }

        $avis = new Avis(0, $produitId, $clientId, $note, $commentaire, new DateTimeImmutable());

        return $this->avisRepository->creer($avis);
    }

    /**
     * Supprime un avis jugé inapproprié — action réservée à
     * l'administrateur, la vérification du rôle étant de la
     * responsabilité du contrôleur qui appelle cette méthode.
     *
     * @param int $id Identifiant de l'avis à supprimer
     */
    public function supprimerAvis(int $id): void
    {
        $this->avisRepository->supprimerParId($id);
    }
}