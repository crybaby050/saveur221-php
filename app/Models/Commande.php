<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\StatutCommande;
use App\Enums\StatutPaiement;
use DateTimeImmutable;

/*
 * Représente une commande passée par un client. statut et statutPaiement
 * évoluent indépendamment l'un de l'autre : une commande peut être RETIREE
 * tout en restant IMPAYE si le client règle plus tard. Les lignes ne sont
 * jamais chargées automatiquement par ce Model : c'est au service de les
 * demander séparément au LigneCommandeRepository, pour ne pas coupler les
 * deux tables au niveau de l'entité elle-même.
 */
final class Commande
{
    /** @var LigneCommande[] */
    private array $lignes = [];

    public function __construct(
        private int $id,
        private string $numeroCommande,
        private int $clientId,
        private DateTimeImmutable $dateCommande,
        private StatutCommande $statut,
        private StatutPaiement $statutPaiement,
        private float $montantTotal,
    ) {
    }

    /**
     * Identifiant unique de la commande en base.
     *
     * @return int Identifiant de la commande
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Référence lisible de la commande (ex: CMD-2026-000231), générée par
     * le service lors de la création.
     *
     * @return string Numéro de commande
     */
    public function getNumeroCommande(): string
    {
        return $this->numeroCommande;
    }

    /**
     * Identifiant du client ayant passé cette commande.
     *
     * @return int Identifiant du client
     */
    public function getClientId(): int
    {
        return $this->clientId;
    }

    /**
     * Date et heure à laquelle la commande a été créée.
     *
     * @return DateTimeImmutable Date de création de la commande
     */
    public function getDateCommande(): DateTimeImmutable
    {
        return $this->dateCommande;
    }

    /**
     * Statut de préparation actuel de la commande.
     *
     * @return StatutCommande Statut courant
     */
    public function getStatut(): StatutCommande
    {
        return $this->statut;
    }

    /**
     * Change le statut de préparation de la commande. La validité de la
     * transition (ex: interdiction de revenir en arrière) est vérifiée par
     * le service, pas ici.
     *
     * @param StatutCommande $statut Nouveau statut à appliquer
     */
    public function changerStatut(StatutCommande $statut): void
    {
        $this->statut = $statut;
    }

    /**
     * Statut de règlement financier de la commande, indépendant de son
     * avancement en préparation.
     *
     * @return StatutPaiement Statut de paiement courant
     */
    public function getStatutPaiement(): StatutPaiement
    {
        return $this->statutPaiement;
    }

    /**
     * Change le statut de paiement de la commande. Appelée uniquement par
     * le service de paiement après enregistrement d'un règlement.
     *
     * @param StatutPaiement $statutPaiement Nouveau statut de paiement
     */
    public function changerStatutPaiement(StatutPaiement $statutPaiement): void
    {
        $this->statutPaiement = $statutPaiement;
    }

    /**
     * Montant total de la commande, en fonction de ses lignes.
     *
     * @return float Montant total
     */
    public function getMontantTotal(): float
    {
        return $this->montantTotal;
    }

    /**
     * Fixe le montant total de la commande. Utilisée par le service après
     * ajout ou retrait de lignes, une fois calculerMontantTotal() appelée.
     *
     * @param float $montantTotal Nouveau montant total
     */
    public function setMontantTotal(float $montantTotal): void
    {
        $this->montantTotal = $montantTotal;
    }

    /**
     * Lignes de produits associées à cette commande. Vide tant
     * qu'ajouterLigne() ou definirLignes() n'a pas été appelée par le
     * service appelant.
     *
     * @return LigneCommande[] Lignes de la commande
     */
    public function getLignes(): array
    {
        return $this->lignes;
    }

    /**
     * Remplace l'ensemble des lignes de la commande, typiquement juste
     * après les avoir chargées depuis le LigneCommandeRepository.
     *
     * @param LigneCommande[] $lignes Nouvelles lignes à associer
     */
    public function definirLignes(array $lignes): void
    {
        $this->lignes = $lignes;
    }

    /**
     * Ajoute une ligne à la commande, typiquement pendant sa construction.
     *
     * @param LigneCommande $ligne Ligne à ajouter
     */
    public function ajouterLigne(LigneCommande $ligne): void
    {
        $this->lignes[] = $ligne;
    }

    /**
     * Calcule le montant total de la commande à partir de ses lignes
     * actuellement chargées. Ne modifie pas montantTotal : c'est à
     * l'appelant de reporter le résultat via setMontantTotal() puis de le
     * persister.
     *
     * @return float Montant total calculé à partir des lignes
     */
    public function calculerMontantTotal(): float
    {
        return array_reduce(
            $this->lignes,
            fn(float $total, LigneCommande $ligne) => $total + $ligne->calculerSousTotal(),
            0.0
        );
    }

    /**
     * Reconstruit une instance de Commande à partir d'une ligne de résultat
     * PDO. Ne charge jamais les lignes associées : voir definirLignes().
     *
     * @param array $ligne Ligne issue de la table commandes
     * @return self Instance correspondant à cette ligne
     */
    public static function depuisLigne(array $ligne): self
    {
        return new self(
            id: (int) $ligne['id'],
            numeroCommande: $ligne['numero_commande'],
            clientId: (int) $ligne['client_id'],
            dateCommande: new DateTimeImmutable($ligne['date_commande']),
            statut: StatutCommande::from($ligne['statut']),
            statutPaiement: StatutPaiement::from($ligne['statut_paiement']),
            montantTotal: (float) $ligne['montant_total'],
        );
    }
}