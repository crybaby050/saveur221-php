<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Enums\StatutCommande;
use App\Models\Commande;
use Core\Database;

/*
 * Accès aux données de la table commandes. Ne charge jamais les lignes
 * d'une commande : c'est la responsabilité de LigneCommandeRepository,
 * appelé séparément depuis le service, pour ne pas coupler les deux
 * tables au niveau de cette classe.
 *
 * statut et statut_paiement sont des types ENUM PostgreSQL : PDO transmet
 * une valeur de l'enum PHP comme une simple chaîne (->value), ce qui
 * fonctionne nativement ici — contrairement à JDBC côté Java, PDO n'exige
 * pas de cast explicite (::statut_commande) dans la requête SQL.
 */
final class CommandeRepository implements RepositoryInterface
{
    private const COLONNES = 'id, numero_commande, client_id, date_commande, statut, statut_paiement, montant_total';

    /**
     * Recherche une commande par son identifiant.
     *
     * @param int $id Identifiant de la commande recherchée
     * @return Commande|null La commande trouvée, ou null si elle n'existe pas
     */
    public function trouverParId(int $id): ?Commande
    {
        $requete = Database::getConnexion()->prepare(
            'SELECT ' . self::COLONNES . ' FROM commandes WHERE id = :id'
        );
        $requete->execute(['id' => $id]);

        $ligne = $requete->fetch();

        return $ligne !== false ? Commande::depuisLigne($ligne) : null;
    }

    /**
     * Retourne toutes les commandes, les plus récentes en premier.
     *
     * @return Commande[] Liste de toutes les commandes
     */
    public function trouverTous(): array
    {
        $requete = Database::getConnexion()->query(
            'SELECT ' . self::COLONNES . ' FROM commandes ORDER BY date_commande DESC'
        );

        return array_map(
            fn(array $ligne) => Commande::depuisLigne($ligne),
            $requete->fetchAll()
        );
    }

    /**
     * Retourne les commandes passées par un client donné, les plus
     * récentes en premier — utilisée pour l'historique côté client et la
     * consultation de l'historique d'un client côté administrateur.
     *
     * @param int $clientId Identifiant du client recherché
     * @return Commande[] Commandes de ce client
     */
    public function trouverParClient(int $clientId): array
    {
        $requete = Database::getConnexion()->prepare(
            'SELECT ' . self::COLONNES . ' FROM commandes WHERE client_id = :clientId ORDER BY date_commande DESC'
        );
        $requete->execute(['clientId' => $clientId]);

        return array_map(
            fn(array $ligne) => Commande::depuisLigne($ligne),
            $requete->fetchAll()
        );
    }

    /**
     * Retourne les commandes ayant un statut donné.
     *
     * @param StatutCommande $statut Statut recherché
     * @return Commande[] Commandes correspondantes
     */
    public function trouverParStatut(StatutCommande $statut): array
    {
        $requete = Database::getConnexion()->prepare(
            'SELECT ' . self::COLONNES . ' FROM commandes WHERE statut = :statut ORDER BY date_commande DESC'
        );
        $requete->execute(['statut' => $statut->value]);

        return array_map(
            fn(array $ligne) => Commande::depuisLigne($ligne),
            $requete->fetchAll()
        );
    }

    /**
     * Retourne les commandes non entièrement réglées (impayées ou
     * partiellement payées).
     *
     * @return Commande[] Commandes impayées ou partiellement payées
     */
    public function trouverImpayeesOuPartielles(): array
    {
        $requete = Database::getConnexion()->query(
            'SELECT ' . self::COLONNES . " FROM commandes
             WHERE statut_paiement IN ('IMPAYE', 'PARTIEL')
             ORDER BY date_commande"
        );

        return array_map(
            fn(array $ligne) => Commande::depuisLigne($ligne),
            $requete->fetchAll()
        );
    }

    /**
     * Recherche une commande par son numéro lisible, plus naturel pour un
     * client ou un gérant qu'un identifiant technique.
     *
     * @param string $numeroCommande Numéro de commande recherché
     * @return Commande|null La commande trouvée, ou null si aucune ne correspond
     */
    public function trouverParNumero(string $numeroCommande): ?Commande
    {
        $requete = Database::getConnexion()->prepare(
            'SELECT ' . self::COLONNES . ' FROM commandes WHERE numero_commande = :numeroCommande'
        );
        $requete->execute(['numeroCommande' => $numeroCommande]);

        $ligne = $requete->fetch();

        return $ligne !== false ? Commande::depuisLigne($ligne) : null;
    }

    /**
     * Insère une nouvelle commande en base.
     *
     * @param Commande $entite Commande à créer (id ignoré)
     * @return Commande La commande créée, avec son identifiant généré
     */
    public function creer(object $entite): Commande
    {
        $requete = Database::getConnexion()->prepare(
            'INSERT INTO commandes (numero_commande, client_id, date_commande, statut, statut_paiement, montant_total)
             VALUES (:numeroCommande, :clientId, :dateCommande, :statut, :statutPaiement, :montantTotal)
             RETURNING id'
        );
        $requete->execute([
            'numeroCommande' => $entite->getNumeroCommande(),
            'clientId' => $entite->getClientId(),
            'dateCommande' => $entite->getDateCommande()->format('Y-m-d H:i:s'),
            'statut' => $entite->getStatut()->value,
            'statutPaiement' => $entite->getStatutPaiement()->value,
            'montantTotal' => $entite->getMontantTotal(),
        ]);

        $id = (int) $requete->fetchColumn();

        return new Commande(
            $id,
            $entite->getNumeroCommande(),
            $entite->getClientId(),
            $entite->getDateCommande(),
            $entite->getStatut(),
            $entite->getStatutPaiement(),
            $entite->getMontantTotal(),
        );
    }

    /**
     * Met à jour le statut, le statut de paiement et le montant total d'une
     * commande. numeroCommande, clientId et dateCommande ne sont jamais
     * modifiés après création.
     *
     * @param Commande $entite Commande contenant les valeurs à jour
     */
    public function mettreAJour(object $entite): void
    {
        $requete = Database::getConnexion()->prepare(
            'UPDATE commandes
             SET statut = :statut, statut_paiement = :statutPaiement, montant_total = :montantTotal
             WHERE id = :id'
        );
        $requete->execute([
            'statut' => $entite->getStatut()->value,
            'statutPaiement' => $entite->getStatutPaiement()->value,
            'montantTotal' => $entite->getMontantTotal(),
            'id' => $entite->getId(),
        ]);
    }

    /**
     * Supprime une commande par son identifiant.
     *
     * @param int $id Identifiant de la commande à supprimer
     */
    public function supprimerParId(int $id): void
    {
        $requete = Database::getConnexion()->prepare('DELETE FROM commandes WHERE id = :id');
        $requete->execute(['id' => $id]);
    }
}