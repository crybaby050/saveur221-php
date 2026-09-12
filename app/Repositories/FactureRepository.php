<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Facture;
use Core\Database;

/*
 * Accès aux données de la table factures.
 */
final class FactureRepository implements RepositoryInterface
{
    private const COLONNES = 'id, numero_facture, commande_id, montant_total, date_emission';

    /**
     * Recherche une facture par son identifiant.
     *
     * @param int $id Identifiant de la facture recherchée
     * @return Facture|null La facture trouvée, ou null si elle n'existe pas
     */
    public function trouverParId(int $id): ?Facture
    {
        $requete = Database::getConnexion()->prepare(
            'SELECT ' . self::COLONNES . ' FROM factures WHERE id = :id'
        );
        $requete->execute(['id' => $id]);

        $ligne = $requete->fetch();

        return $ligne !== false ? Facture::depuisLigne($ligne) : null;
    }

    /**
     * Retourne toutes les factures, les plus récentes en premier.
     *
     * @return Facture[] Liste de toutes les factures
     */
    public function trouverTous(): array
    {
        $requete = Database::getConnexion()->query(
            'SELECT ' . self::COLONNES . ' FROM factures ORDER BY date_emission DESC'
        );

        return array_map(
            fn(array $ligne) => Facture::depuisLigne($ligne),
            $requete->fetchAll()
        );
    }

    /**
     * Retourne la facture associée à une commande donnée, relation 1-1
     * garantie par une contrainte UNIQUE en base.
     *
     * @param int $commandeId Identifiant de la commande recherchée
     * @return Facture|null La facture trouvée, ou null si la commande n'en a pas
     */
    public function trouverParCommande(int $commandeId): ?Facture
    {
        $requete = Database::getConnexion()->prepare(
            'SELECT ' . self::COLONNES . ' FROM factures WHERE commande_id = :commandeId'
        );
        $requete->execute(['commandeId' => $commandeId]);

        $ligne = $requete->fetch();

        return $ligne !== false ? Facture::depuisLigne($ligne) : null;
    }

    /**
     * Recherche une facture par son numéro lisible.
     *
     * @param string $numeroFacture Numéro de facture recherché
     * @return Facture|null La facture trouvée, ou null si aucune ne correspond
     */
    public function trouverParNumero(string $numeroFacture): ?Facture
    {
        $requete = Database::getConnexion()->prepare(
            'SELECT ' . self::COLONNES . ' FROM factures WHERE numero_facture = :numeroFacture'
        );
        $requete->execute(['numeroFacture' => $numeroFacture]);

        $ligne = $requete->fetch();

        return $ligne !== false ? Facture::depuisLigne($ligne) : null;
    }

    /**
     * Insère une nouvelle facture en base.
     *
     * @param Facture $entite Facture à créer (id ignoré)
     * @return Facture La facture créée, avec son identifiant généré
     */
    public function creer(object $entite): Facture
    {
        $requete = Database::getConnexion()->prepare(
            'INSERT INTO factures (numero_facture, commande_id, montant_total, date_emission)
             VALUES (:numeroFacture, :commandeId, :montantTotal, :dateEmission)
             RETURNING id'
        );
        $requete->execute([
            'numeroFacture' => $entite->getNumeroFacture(),
            'commandeId' => $entite->getCommandeId(),
            'montantTotal' => $entite->getMontantTotal(),
            'dateEmission' => $entite->getDateEmission()->format('Y-m-d H:i:s'),
        ]);

        $id = (int) $requete->fetchColumn();

        return new Facture(
            $id,
            $entite->getNumeroFacture(),
            $entite->getCommandeId(),
            $entite->getMontantTotal(),
            $entite->getDateEmission(),
        );
    }

    /**
     * Met à jour le montant total d'une facture. Une facture émise n'a
     * normalement pas vocation à être modifiée (valeur comptable figée) —
     * fournie pour respecter le contrat RepositoryInterface, non utilisée
     * par le service à ce stade.
     *
     * @param Facture $entite Facture contenant les valeurs à jour
     */
    public function mettreAJour(object $entite): void
    {
        $requete = Database::getConnexion()->prepare(
            'UPDATE factures SET montant_total = :montantTotal WHERE id = :id'
        );
        $requete->execute(['montantTotal' => $entite->getMontantTotal(), 'id' => $entite->getId()]);
    }

    /**
     * Supprime une facture par son identifiant.
     *
     * @param int $id Identifiant de la facture à supprimer
     */
    public function supprimerParId(int $id): void
    {
        $requete = Database::getConnexion()->prepare('DELETE FROM factures WHERE id = :id');
        $requete->execute(['id' => $id]);
    }
}