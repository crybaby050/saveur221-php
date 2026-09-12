<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Enums\TypePaiementRecu;
use App\Models\Recu;
use Core\Database;

final class RecuRepository implements RepositoryInterface
{
    private const COLONNES = 'id, numero_recu, paiement_id, type_paiement, montant, date_emission';

    /**
     * Recherche un reçu par son identifiant.
     *
     * @param int $id Identifiant du reçu recherché
     * @return Recu|null Le reçu trouvé, ou null s'il n'existe pas
     */
    public function trouverParId(int $id): ?Recu
    {
        $requete = Database::getConnexion()->prepare(
            'SELECT ' . self::COLONNES . ' FROM recus WHERE id = :id'
        );
        $requete->execute(['id' => $id]);

        $ligne = $requete->fetch();

        return $ligne !== false ? Recu::depuisLigne($ligne) : null;
    }

    /**
     * Retourne tous les reçus, les plus récents en premier.
     *
     * @return Recu[] Liste de tous les reçus
     */
    public function trouverTous(): array
    {
        $requete = Database::getConnexion()->query(
            'SELECT ' . self::COLONNES . ' FROM recus ORDER BY date_emission DESC'
        );

        return array_map(
            fn(array $ligne) => Recu::depuisLigne($ligne),
            $requete->fetchAll()
        );
    }

    /**
     * Retourne le reçu associé à un paiement donné, relation 1-1 garantie
     * par une contrainte UNIQUE en base.
     *
     * @param int $paiementId Identifiant du paiement recherché
     * @return Recu|null Le reçu trouvé, ou null si le paiement n'en a pas
     */
    public function trouverParPaiement(int $paiementId): ?Recu
    {
        $requete = Database::getConnexion()->prepare(
            'SELECT ' . self::COLONNES . ' FROM recus WHERE paiement_id = :paiementId'
        );
        $requete->execute(['paiementId' => $paiementId]);

        $ligne = $requete->fetch();

        return $ligne !== false ? Recu::depuisLigne($ligne) : null;
    }

    /**
     * Retourne tous les reçus liés à une commande. Une commande peut avoir
     * plusieurs reçus (un par paiement) — jointure nécessaire puisque
     * recus ne connaît pas directement commande_id.
     *
     * @param int $commandeId Identifiant de la commande recherchée
     * @return Recu[] Reçus de cette commande, dans l'ordre chronologique
     */
    public function trouverParCommande(int $commandeId): array
    {
        $requete = Database::getConnexion()->prepare(
            'SELECT r.id, r.numero_recu, r.paiement_id, r.type_paiement, r.montant, r.date_emission
             FROM recus r JOIN paiements p ON r.paiement_id = p.id
             WHERE p.commande_id = :commandeId
             ORDER BY r.date_emission'
        );
        $requete->execute(['commandeId' => $commandeId]);

        return array_map(
            fn(array $ligne) => Recu::depuisLigne($ligne),
            $requete->fetchAll()
        );
    }

    /**
     * Insère un nouveau reçu en base.
     *
     * @param Recu $entite Reçu à créer (id ignoré)
     * @return Recu Le reçu créé, avec son identifiant généré
     */
    public function creer(object $entite): Recu
    {
        $requete = Database::getConnexion()->prepare(
            'INSERT INTO recus (numero_recu, paiement_id, type_paiement, montant, date_emission)
             VALUES (:numeroRecu, :paiementId, :typePaiement, :montant, :dateEmission)
             RETURNING id'
        );
        $requete->execute([
            'numeroRecu' => $entite->getNumeroRecu(),
            'paiementId' => $entite->getPaiementId(),
            'typePaiement' => $entite->getTypePaiement()->value,
            'montant' => $entite->getMontant(),
            'dateEmission' => $entite->getDateEmission()->format('Y-m-d H:i:s'),
        ]);

        $id = (int) $requete->fetchColumn();

        return new Recu(
            $id,
            $entite->getNumeroRecu(),
            $entite->getPaiementId(),
            $entite->getTypePaiement(),
            $entite->getMontant(),
            $entite->getDateEmission(),
        );
    }

    /**
     * Met à jour le type de paiement d'un reçu. Un reçu émis n'a pas
     * vocation à être modifié — fournie pour respecter le contrat
     * RepositoryInterface, non utilisée en pratique.
     *
     * @param Recu $entite Reçu contenant les valeurs à jour
     */
    public function mettreAJour(object $entite): void
    {
        $requete = Database::getConnexion()->prepare(
            'UPDATE recus SET type_paiement = :typePaiement WHERE id = :id'
        );
        $requete->execute(['typePaiement' => $entite->getTypePaiement()->value, 'id' => $entite->getId()]);
    }

    /**
     * Supprime un reçu par son identifiant.
     *
     * @param int $id Identifiant du reçu à supprimer
     */
    public function supprimerParId(int $id): void
    {
        $requete = Database::getConnexion()->prepare('DELETE FROM recus WHERE id = :id');
        $requete->execute(['id' => $id]);
    }
}