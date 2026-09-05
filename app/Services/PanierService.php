<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ProduitInexistantException;
use App\Exceptions\StockInsuffisantException;
use App\Repositories\ProduitRepository;
use Core\Session;

/*
 * Gère le panier d'un client avant validation de la commande. Le panier
 * n'a pas d'entité ni de table dédiées : il vit entièrement en session,
 * sous la forme d'un tableau associatif [produitId => quantite]. C'est
 * volontairement léger, puisque son contenu n'a de valeur qu'avant d'être
 * transformé en véritable Commande par CommandeService.
 */
final class PanierService
{
    private const CLE_SESSION_PANIER = 'panier';

    public function __construct(
        private readonly ProduitRepository $produitRepository,
    ) {
    }

    /**
     * Retourne le contenu détaillé du panier : pour chaque produit encore
     * présent en session, ses informations à jour (prix, disponibilité)
     * accompagnées de la quantité demandée et du sous-total correspondant.
     * Un produit supprimé du catalogue depuis son ajout au panier est
     * silencieusement écarté du résultat plutôt que de faire échouer
     * l'affichage.
     *
     * @return array<int, array{produit: \App\Models\Produit, quantite: int, sousTotal: float}>
     *         Contenu du panier, indexé par identifiant de produit
     */
    public function contenu(): array
    {
        $panier = Session::get(self::CLE_SESSION_PANIER, []);
        $resultat = [];

        foreach ($panier as $produitId => $quantite) {
            $produit = $this->produitRepository->trouverParId($produitId);

            if ($produit === null) {
                continue;
            }

            $resultat[$produitId] = [
                'produit' => $produit,
                'quantite' => $quantite,
                'sousTotal' => $produit->getPrix() * $quantite,
            ];
        }

        return $resultat;
    }

    /**
     * Calcule le montant total du panier, tous produits confondus.
     *
     * @return float Montant total du panier
     */
    public function montantTotal(): float
    {
        return array_reduce(
            $this->contenu(),
            fn(float $total, array $ligne) => $total + $ligne['sousTotal'],
            0.0
        );
    }

    /**
     * Ajoute un produit au panier, ou augmente sa quantité s'il y figure
     * déjà. Vérifie que la quantité totale demandée (existante plus
     * nouvelle) ne dépasse pas le stock disponible.
     *
     * @param int $produitId Identifiant du produit à ajouter
     * @param int $quantite  Quantité à ajouter
     *
     * @throws ProduitInexistantException si le produit n'existe pas
     * @throws StockInsuffisantException si la quantité demandée dépasse le stock
     */
    public function ajouter(int $produitId, int $quantite): void
    {
        $produit = $this->produitRepository->trouverParId($produitId);

        if ($produit === null) {
            throw new ProduitInexistantException("Produit introuvable avec l'id {$produitId}.");
        }

        $panier = Session::get(self::CLE_SESSION_PANIER, []);
        $quantiteExistante = $panier[$produitId] ?? 0;
        $nouvelleQuantite = $quantiteExistante + $quantite;

        if ($nouvelleQuantite > $produit->getQuantiteStock()) {
            throw new StockInsuffisantException(
                "Stock insuffisant pour {$produit->getLibelle()} (disponible : {$produit->getQuantiteStock()})."
            );
        }

        $panier[$produitId] = $nouvelleQuantite;
        Session::set(self::CLE_SESSION_PANIER, $panier);
    }

    /**
     * Remplace directement la quantité d'un produit déjà présent dans le
     * panier, plutôt que de l'incrémenter — utilisée lorsque le client
     * ajuste la quantité depuis la page panier (ex: passer de 2 à 5).
     *
     * @param int $produitId Identifiant du produit concerné
     * @param int $quantite  Nouvelle quantité souhaitée
     *
     * @throws ProduitInexistantException si le produit n'existe pas
     * @throws StockInsuffisantException si la quantité demandée dépasse le stock
     */
    public function modifierQuantite(int $produitId, int $quantite): void
    {
        $produit = $this->produitRepository->trouverParId($produitId);

        if ($produit === null) {
            throw new ProduitInexistantException("Produit introuvable avec l'id {$produitId}.");
        }

        if ($quantite > $produit->getQuantiteStock()) {
            throw new StockInsuffisantException(
                "Stock insuffisant pour {$produit->getLibelle()} (disponible : {$produit->getQuantiteStock()})."
            );
        }

        $panier = Session::get(self::CLE_SESSION_PANIER, []);
        $panier[$produitId] = $quantite;
        Session::set(self::CLE_SESSION_PANIER, $panier);
    }

    /**
     * Retire un produit du panier, quelle que soit sa quantité actuelle.
     *
     * @param int $produitId Identifiant du produit à retirer
     */
    public function retirer(int $produitId): void
    {
        $panier = Session::get(self::CLE_SESSION_PANIER, []);
        unset($panier[$produitId]);
        Session::set(self::CLE_SESSION_PANIER, $panier);
    }

    /**
     * Vide entièrement le panier — utilisée en cas de demande explicite du
     * client, ou automatiquement après la validation réussie d'une
     * commande (voir CommandeService::validerPanier).
     */
    public function vider(): void
    {
        Session::remove(self::CLE_SESSION_PANIER);
    }

    /**
     * Indique si le panier est actuellement vide, utilisée par
     * CommandeService avant de tenter de transformer le panier en
     * commande.
     *
     * @return bool true si le panier ne contient aucun produit
     */
    public function estVide(): bool
    {
        return empty(Session::get(self::CLE_SESSION_PANIER, []));
    }
}