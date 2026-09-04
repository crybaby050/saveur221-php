<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Avis;
use Core\Database;

/*
 * Accès aux données de la table avis. La contrainte d'unicité sur
 * commande_id (un seul avis par commande) est garantie au niveau base ;
 * ce repository expose une méthode dédiée pour que le service puisse
 * vérifier cette règle avant insertion et afficher une erreur explicite
 * plutôt que de laisser échouer la contrainte SQL brutalement.
 */
final class AvisRepository implements RepositoryInterface
{
    private const COLONNES = 'id, commande_id, client_id, note, commentaire, date_avis';

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
     * Retourne l'avis associé à une commande donnée, relation 1-1 garantie
     * par une contrainte UNIQUE en base.
     *
     * @param int $commandeId Identifiant de la commande recherchée
     * @return Avis|null L'avis trouvé, ou null si la commande n'en a pas encore
     */
    public function trouverParCommande(int $commandeId): ?Avis
    {
        $requete = Database::getConnexion()->prepare(
            'SELECT ' . self::COLONNES . ' FROM avis WHERE commande_id = :commandeId'
        );
        $requete->execute(['commandeId' => $commandeId]);

        $ligne = $requete->fetch();

        return $ligne !== false ? Avis::depuisLigne($ligne) : null;
    }

    /**
     * Retourne les avis filtrés par note exacte — utilisée pour le filtrage
     * par note côté administrateur.
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
     * Vérifie si une commande possède déjà un avis, pour faire respecter
     * la règle métier "un seul avis par commande" avant toute tentative
     * d'insertion.
     *
     * @param int $commandeId Identifiant de la commande à vérifier
     * @return bool true si un avis existe déjà pour cette commande
     */
    public function existeParCommande(int $commandeId): bool
    {
        $requete = Database::getConnexion()->prepare(
            'SELECT COUNT(*) FROM avis WHERE commande_id = :commandeId'
        );
        $requete->execute(['commandeId' => $commandeId]);

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
            'INSERT INTO avis (commande_id, client_id, note, commentaire, date_avis)
             VALUES (:commandeId, :clientId, :note, :commentaire, :dateAvis)
             RETURNING id'
        );
        $requete->execute([
            'commandeId' => $entite->getCommandeId(),
            'clientId' => $entite->getClientId(),
            'note' => $entite->getNote(),
            'commentaire' => $entite->getCommentaire(),
            'dateAvis' => $entite->getDateAvis()->format('Y-m-d H:i:s'),
        ]);

        $id = (int) $requete->fetchColumn();

        return new Avis(
            $id,
            $entite->getCommandeId(),
            $entite->getClientId(),
            $entite->getNote(),
            $entite->getCommentaire(),
            $entite->getDateAvis(),
        );
    }

    /**
     * Met à jour un avis existant. Le sujet ne prévoit pas d'édition d'avis
     * par le client — fournie pour respecter le contrat
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