<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Avis;
use Core\Database;

final class AvisRepository implements RepositoryInterface
{
    private const COLONNES = 'id, produit_id, client_id, note, commentaire, date_avis';

    /**
     * Recherche un avis par son identifiant.
     *
     * @param int $id Identifiant de l'avis recherché
     * @return Avis|null L'avis trouvé, ou null s'il n'existe pas
     */
    public function trouverParId(int $id): ?Avis
    {
        $requete = Database::getConnexion()->prepare(
            'SELECT ' . self::COLONNES . ' FROM avis WHERE id = :id'
        );
        $requete->execute(['id' => $id]);

        $ligne = $requete->fetch();

        return $ligne !== false ? Avis::depuisLigne($ligne) : null;
    }

    /**
     * Retourne tous les avis, les plus récents en premier — utilisée pour
     * la modération côté administrateur.
     *
     * @return Avis[] Liste de tous les avis
     */
    public function trouverTous(): array
    {
        $requete = Database::getConnexion()->query(
            'SELECT ' . self::COLONNES . ' FROM avis ORDER BY date_avis DESC'
        );

        return array_map(
            fn(array $ligne) => Avis::depuisLigne($ligne),
            $requete->fetchAll()
        );
    }

    /**
     * Retourne tous les avis déposés sur un produit donné — utilisée pour
     * afficher les avis clients sur la fiche produit du catalogue.
     *
     * @param int $produitId Identifiant du produit recherché
     * @return Avis[] Avis déposés sur ce produit, les plus récents en premier
     */
    public function trouverParProduit(int $produitId): array
    {
        $requete = Database::getConnexion()->prepare(
            'SELECT ' . self::COLONNES . ' FROM avis WHERE produit_id = :produitId ORDER BY date_avis DESC'
        );
        $requete->execute(['produitId' => $produitId]);

        return array_map(
            fn(array $ligne) => Avis::depuisLigne($ligne),
            $requete->fetchAll()
        );
    }

    /**
     * Calcule la note moyenne d'un produit, arrondie à une décimale.
     * Retourne null si le produit n'a encore reçu aucun avis, pour
     * distinguer ce cas d'une note de 0.
     *
     * @param int $produitId Identifiant du produit recherché
     * @return float|null Note moyenne sur 5, ou null si aucun avis
     */
    public function noteMoyenne(int $produitId): ?float
    {
        $requete = Database::getConnexion()->prepare(
            'SELECT ROUND(AVG(note)::numeric, 1) FROM avis WHERE produit_id = :produitId'
        );
        $requete->execute(['produitId' => $produitId]);

        $moyenne = $requete->fetchColumn();

        return $moyenne !== null ? (float) $moyenne : null;
    }

    /**
     * Retourne les avis ayant une note exacte donnée.
     *
     * @param int $note Note recherchée (entre 1 et 5)
     * @return Avis[] Avis correspondant à cette note
     */
    public function trouverParNote(int $note): array
    {
        $requete = Database::getConnexion()->prepare(
            'SELECT ' . self::COLONNES . ' FROM avis WHERE note = :note ORDER BY date_avis DESC'
        );
        $requete->execute(['note' => $note]);

        return array_map(
            fn(array $ligne) => Avis::depuisLigne($ligne),
            $requete->fetchAll()
        );
    }

    /**
     * Vérifie si un client a déjà déposé un avis sur un produit donné,
     * pour faire respecter la règle métier "un seul avis par produit et
     * par client" avant toute tentative d'insertion.
     *
     * @param int $clientId  Identifiant du client à vérifier
     * @param int $produitId Identifiant du produit à vérifier
     * @return bool true si un avis existe déjà pour ce couple client/produit
     */
    public function existeParClientEtProduit(int $clientId, int $produitId): bool
    {
        $requete = Database::getConnexion()->prepare(
            'SELECT COUNT(*) FROM avis WHERE client_id = :clientId AND produit_id = :produitId'
        );
        $requete->execute(['clientId' => $clientId, 'produitId' => $produitId]);

        return ((int) $requete->fetchColumn()) > 0;
    }

    /**
     * Vérifie qu'un client a bien commandé un produit donné, dans une
     * commande déjà au statut RETIREE — condition requise avant de
     * pouvoir déposer un avis. La jointure passe par ligne_commandes,
     * puisque avis ne référence plus directement une commande précise.
     *
     * @param int $clientId  Identifiant du client à vérifier
     * @param int $produitId Identifiant du produit à vérifier
     * @return bool true si au moins une commande retirée contient ce produit
     */
    public function aCommandeEtRetireProduit(int $clientId, int $produitId): bool
    {
        $requete = Database::getConnexion()->prepare(
            "SELECT COUNT(*) FROM ligne_commandes lc
             JOIN commandes c ON lc.commande_id = c.id
             WHERE c.client_id = :clientId
               AND lc.produit_id = :produitId
               AND c.statut = 'RETIREE'"
        );
        $requete->execute(['clientId' => $clientId, 'produitId' => $produitId]);

        return ((int) $requete->fetchColumn()) > 0;
    }

    /**
     * Insère un nouvel avis en base.
     *
     * @param Avis $entite Avis à créer (id ignoré)
     * @return Avis L'avis créé, avec son identifiant généré
     */
    public function creer(object $entite): Avis
    {
        $requete = Database::getConnexion()->prepare(
            'INSERT INTO avis (produit_id, client_id, note, commentaire, date_avis)
             VALUES (:produitId, :clientId, :note, :commentaire, :dateAvis)
             RETURNING id'
        );
        $requete->execute([
            'produitId' => $entite->getProduitId(),
            'clientId' => $entite->getClientId(),
            'note' => $entite->getNote(),
            'commentaire' => $entite->getCommentaire(),
            'dateAvis' => $entite->getDateAvis()->format('Y-m-d H:i:s'),
        ]);

        $id = (int) $requete->fetchColumn();

        return new Avis(
            $id,
            $entite->getProduitId(),
            $entite->getClientId(),
            $entite->getNote(),
            $entite->getCommentaire(),
            $entite->getDateAvis(),
        );
    }

    /**
     * Met à jour un avis existant. Le sujet ne prévoit pas d'édition
     * d'avis par le client — fournie pour respecter le contrat
     * RepositoryInterface, non utilisée par le service à ce stade.
     *
     * @param Avis $entite Avis contenant les valeurs à jour
     */
    public function mettreAJour(object $entite): void
    {
        $requete = Database::getConnexion()->prepare(
            'UPDATE avis SET note = :note, commentaire = :commentaire WHERE id = :id'
        );
        $requete->execute([
            'note' => $entite->getNote(),
            'commentaire' => $entite->getCommentaire(),
            'id' => $entite->getId(),
        ]);
    }

    /**
     * Supprime un avis par son identifiant — utilisée par l'administrateur
     * pour retirer un avis jugé inapproprié.
     *
     * @param int $id Identifiant de l'avis à supprimer
     */
    public function supprimerParId(int $id): void
    {
        $requete = Database::getConnexion()->prepare('DELETE FROM avis WHERE id = :id');
        $requete->execute(['id' => $id]);
    }
}