<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Paiement;
use Core\Database;

/*
 * Accès aux données de la table paiements.
 */
final class PaiementRepository implements RepositoryInterface
{
    private const COLONNES = 'id, commande_id, montant, date_paiement';

    /**
     * Recherche un paiement par son identifiant.
     *
     * @param int $id Identifiant du paiement recherché
     * @return Paiement|null Le paiement trouvé, ou null s'il n'existe pas
     */
    public function trouverParId(int $id): ?Paiement
    {
        $requete = Database::getConnexion()->prepare(
            'SELECT ' . self::COLONNES . ' FROM paiements WHERE id = :id'
        );
        $requete->execute(['id' => $id]);

        $ligne = $requete->fetch();

        return $ligne !== false ? Paiement::depuisLigne($ligne) : null;
    }

    /**
     * Retourne tous les paiements, les plus récents en premier.
     *
     * @return Paiement[] Liste de tous les paiements
     */
    public function trouverTous(): array
    {
        $requete = Database::getConnexion()->query(
            'SELECT ' . self::COLONNES . ' FROM paiements ORDER BY date_paiement DESC'
        );

        return array_map(
            fn(array $ligne) => Paiement::depuisLigne($ligne),
            $requete->fetchAll()
        );
    }

    /**
     * Retourne l'historique des paiements d'une commande, dans l'ordre
     * chronologique.
     *
     * @param int $commandeId Identifiant de la commande recherchée
     * @return Paiement[] Paiements de cette commande
     */
    public function trouverParCommande(int $commandeId): array
    {
        $requete = Database::getConnexion()->prepare(
            'SELECT ' . self::COLONNES . ' FROM paiements WHERE commande_id = :commandeId ORDER BY date_paiement'
        );
        $requete->execute(['commandeId' => $commandeId]);

        return array_map(
            fn(array $ligne) => Paiement::depuisLigne($ligne),
            $requete->fetchAll()
        );
    }

    /**
     * Calcule le total déjà payé pour une commande donnée. Utilisée par le
     * service avant d'accepter un nouveau paiement, pour appliquer la règle
     * métier interdisant de dépasser le montant restant.
     *
     * @param int $commandeId Identifiant de la commande concernée
     * @return float Somme des paiements déjà enregistrés pour cette commande
     */
    public function sommePaiements(int $commandeId): float
    {
        $requete = Database::getConnexion()->prepare(
            'SELECT COALESCE(SUM(montant), 0) FROM paiements WHERE commande_id = :commandeId'
        );
        $requete->execute(['commandeId' => $commandeId]);

        return (float) $requete->fetchColumn();
    }

    /**
     * Insère un nouveau paiement en base.
     *
     * @param Paiement $entite Paiement à créer (id ignoré)
     * @return Paiement Le paiement créé, avec son identifiant généré
     */
    public function creer(object $entite): Paiement
    {
        $requete = Database::getConnexion()->prepare(
            'INSERT INTO paiements (commande_id, montant, date_paiement)
             VALUES (:commandeId, :montant, :datePaiement)
             RETURNING id'
        );
        $requete->execute([
            'commandeId' => $entite->getCommandeId(),
            'montant' => $entite->getMontant(),
            'datePaiement' => $entite->getDatePaiement()->format('Y-m-d H:i:s'),
        ]);

        $id = (int) $requete->fetchColumn();

        return new Paiement($id, $entite->getCommandeId(), $entite->getMontant(), $entite->getDatePaiement());
    }

    /**
     * Met à jour le montant d'un paiement. Un paiement déjà enregistré n'a
     * normalement pas vocation à être modifié (traçabilité comptable) —
     * fournie pour respecter le contrat RepositoryInterface, non utilisée
     * par le service à ce stade.
     *
     * @param Paiement $entite Paiement contenant les valeurs à jour
     */
    public function mettreAJour(object $entite): void
    {
        $requete = Database::getConnexion()->prepare(
            'UPDATE paiements SET montant = :montant WHERE id = :id'
        );
        $requete->execute(['montant' => $entite->getMontant(), 'id' => $entite->getId()]);
    }

    /**
     * Supprime un paiement par son identifiant.
     *
     * @param int $id Identifiant du paiement à supprimer
     */
    public function supprimerParId(int $id): void
    {
        $requete = Database::getConnexion()->prepare('DELETE FROM paiements WHERE id = :id');
        $requete->execute(['id' => $id]);
    }
}