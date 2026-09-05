<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\StatutCommande;
use App\Exceptions\AccesRefuseException;
use App\Exceptions\AvisDejaDeposeException;
use App\Exceptions\AvisNonAutoriseException;
use App\Exceptions\CommandeInexistanteException;
use App\Models\Avis;
use App\Repositories\AvisRepository;
use App\Repositories\CommandeRepository;
use DateTimeImmutable;

/*
 * Applique les règles métier liées aux avis clients : dépôt (réservé au
 * propriétaire de la commande, une fois celle-ci retirée) et modération
 * côté administrateur.
 */
final class AvisService
{
    private const NOTE_MIN = 1;
    private const NOTE_MAX = 5;

    public function __construct(
        private readonly AvisRepository $avisRepository,
        private readonly CommandeRepository $commandeRepository,
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
     * Récupère l'avis associé à une commande, s'il existe.
     *
     * @param int $commandeId Identifiant de la commande recherchée
     * @return Avis|null L'avis trouvé, ou null si la commande n'en a pas encore
     */
    public function consulterParCommande(int $commandeId): ?Avis
    {
        return $this->avisRepository->trouverParCommande($commandeId);
    }

    /**
     * Dépose un avis sur une commande, après vérification de l'ensemble
     * des règles métier : la commande doit appartenir au client qui
     * dépose l'avis, être au statut RETIREE, et n'avoir reçu aucun avis
     * auparavant.
     *
     * @param int         $clientId    Identifiant du client déposant l'avis
     * @param int         $commandeId  Identifiant de la commande concernée
     * @param int         $note        Note attribuée, doit être comprise entre 1 et 5
     * @param string|null $commentaire Commentaire facultatif
     * @return Avis L'avis créé
     *
     * @throws CommandeInexistanteException si la commande n'existe pas
     * @throws AccesRefuseException si la commande n'appartient pas à ce client
     * @throws AvisNonAutoriseException si la commande n'est pas au statut RETIREE
     * @throws AvisDejaDeposeException si un avis existe déjà pour cette commande
     * @throws \InvalidArgumentException si la note est hors de la plage 1 à 5
     */
    public function deposerAvis(int $clientId, int $commandeId, int $note, ?string $commentaire): Avis
    {
        if ($note < self::NOTE_MIN || $note > self::NOTE_MAX) {
            throw new \InvalidArgumentException('La note doit être comprise entre 1 et 5.');
        }

        $commande = $this->commandeRepository->trouverParId($commandeId);

        if ($commande === null) {
            throw new CommandeInexistanteException("Commande introuvable avec l'id {$commandeId}.");
        }

        if ($commande->getClientId() !== $clientId) {
            throw new AccesRefuseException('Cette commande ne vous appartient pas.');
        }

        if ($commande->getStatut() !== StatutCommande::RETIREE) {
            throw new AvisNonAutoriseException('Un avis ne peut être déposé qu\'après le retrait de la commande.');
        }

        if ($this->avisRepository->existeParCommande($commandeId)) {
            throw new AvisDejaDeposeException('Un avis a déjà été déposé pour cette commande.');
        }

        $avis = new Avis(0, $commandeId, $clientId, $note, $commentaire, new DateTimeImmutable());

        return $this->avisRepository->creer($avis);
    }

    /**
     * Supprime un avis jugé inapproprié — action réservée à
     * l'administrateur, la vérification du rôle étant de la responsabilité
     * du contrôleur qui appelle cette méthode.
     *
     * @param int $id Identifiant de l'avis à supprimer
     */
    public function supprimerAvis(int $id): void
    {
        $this->avisRepository->supprimerParId($id);
    }
}