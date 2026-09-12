<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\CategorieUtiliseeException;
use App\Models\Categorie;
use App\Repositories\CategorieRepository;
use App\Repositories\ProduitRepository;

/*
 * Applique les règles métier liées aux catégories. Reçoit ses repositories
 * par injection de constructeur plutôt que de les instancier lui-même —
 * même principe d'inversion de contrôle que côté Java Console, ici
 * orchestré par Core\Container au moment de la construction du service.
 */
final class CategorieService
{
    /*
     * Illustration affichée pour toute catégorie n'ayant pas reçu sa
     * propre image — un simple visuel neutre en attendant que le projet
     * dispose de sa propre illustration par défaut.
     */
    private const IMAGE_PAR_DEFAUT = '/assets/images/categorie-defaut.svg';

    /*
     * Nombre maximal de tentatives de tirage d'une couleur aléatoire avant
     * d'abandonner — protection contre une boucle infinie dans le cas
     * (extrêmement improbable) où le hasard retomberait sans cesse sur des
     * couleurs déjà attribuées.
     */
    private const TENTATIVES_MAX_COULEUR = 20;

    public function __construct(
        private readonly CategorieRepository $categorieRepository,
        private readonly ProduitRepository $produitRepository,
    ) {
    }

    /**
     * Retourne toutes les catégories existantes.
     *
     * @return Categorie[] Liste de toutes les catégories
     */
    public function listerCategories(): array
    {
        return $this->categorieRepository->trouverTous();
    }

    /**
     * Récupère une catégorie par son identifiant.
     *
     * @param int $id Identifiant de la catégorie recherchée
     * @return Categorie|null La catégorie trouvée, ou null si elle n'existe pas
     */
    public function consulterCategorie(int $id): ?Categorie
    {
        return $this->categorieRepository->trouverParId($id);
    }

    /**
     * Recherche les catégories dont le nom contient le mot-clé fourni.
     *
     * @param string $motCle Fragment de nom recherché
     * @return Categorie[] Catégories correspondantes
     */
    public function rechercherCategorie(string $motCle): array
    {
        return $this->categorieRepository->rechercherParNom($motCle);
    }

    /**
     * Crée une nouvelle catégorie. Si aucune couleur n'est fournie, une
     * couleur hexadécimale est générée aléatoirement (parmi l'ensemble des
     * couleurs possibles, pas une palette restreinte) jusqu'à en trouver
     * une qui ne soit pas déjà attribuée. Si aucune illustration n'est
     * fournie, une image générique par défaut est utilisée à sa place.
     *
     * @param string      $nom         Nom de la catégorie
     * @param string|null $description Description facultative
     * @param string|null $image       URL ou chemin de l'illustration, ou
     *                                  null pour utiliser l'image par défaut
     * @param string|null $couleur     Code hexadécimal choisi, ou null pour
     *                                  qu'une couleur soit générée au hasard
     * @return Categorie La catégorie créée
     */
    public function ajouterCategorie(
        string $nom,
        ?string $description,
        ?string $image = null,
        ?string $couleur = null,
    ): Categorie {
        $couleurRetenue = $couleur ?? $this->genererCouleurDisponible();
        $imageRetenue = $image ?? self::IMAGE_PAR_DEFAUT;

        $categorie = new Categorie(0, $nom, $description, $imageRetenue, $couleurRetenue);

        return $this->categorieRepository->creer($categorie);
    }

    /**
     * Modifie le nom, la description, l'illustration et la colorimétrie
     * d'une catégorie existante en une seule opération.
     *
     * @param int         $id          Identifiant de la catégorie à modifier
     * @param string      $nom         Nouveau nom
     * @param string|null $description Nouvelle description
     * @param string|null $image       Nouvelle illustration, ou null pour
     *                                  revenir à l'image par défaut
     * @param string|null $couleur     Nouvelle couleur, ou null pour en
     *                                  générer une nouvelle au hasard
     *
     * @throws \InvalidArgumentException si la catégorie n'existe pas
     */
    public function modifierCategorie(
        int $id,
        string $nom,
        ?string $description,
        ?string $image = null,
        ?string $couleur = null,
    ): void {
        $categorie = $this->trouverOuLever($id);

        $categorie->setNom($nom);
        $categorie->setDescription($description);
        $categorie->setImage($image ?? self::IMAGE_PAR_DEFAUT);
        $categorie->setCouleur($couleur ?? $this->genererCouleurDisponible($id));

        $this->categorieRepository->mettreAJour($categorie);
    }

    /**
     * Supprime une catégorie, après avoir vérifié qu'elle ne contient plus
     * aucun produit.
     *
     * @param int $id Identifiant de la catégorie à supprimer
     *
     * @throws CategorieUtiliseeException si la catégorie contient encore
     *                                     des produits
     */
    public function supprimerCategorie(int $id): void
    {
        $produitsLies = $this->produitRepository->trouverParCategorie($id);

        if (count($produitsLies) > 0) {
            throw new CategorieUtiliseeException(
                'Impossible de supprimer cette catégorie : elle contient encore des produits.'
            );
        }

        $this->categorieRepository->supprimerParId($id);
    }

    /**
     * Génère une couleur hexadécimale aléatoire parmi l'ensemble des
     * couleurs possibles (pas de palette restreinte), en s'assurant
     * qu'elle n'est pas déjà attribuée à une autre catégorie. exclureId
     * permet, lors d'une modification, de ne pas tenir compte de la
     * couleur actuelle de la catégorie elle-même.
     *
     * @param int|null $exclureId Identifiant de catégorie à exclure de la
     *                             vérification d'unicité
     * @return string Code hexadécimal disponible (ex: #A3D4F1)
     *
     * @throws \RuntimeException si aucune couleur disponible n'a été
     *                            trouvée après plusieurs tentatives
     */
    private function genererCouleurDisponible(?int $exclureId = null): string
    {
        for ($tentative = 0; $tentative < self::TENTATIVES_MAX_COULEUR; $tentative++) {
            $couleur = sprintf('#%06X', random_int(0, 0xFFFFFF));

            if (!$this->categorieRepository->couleurDejaUtilisee($couleur, $exclureId)) {
                return $couleur;
            }
        }

        throw new \RuntimeException(
            "Impossible de générer une couleur disponible après " . self::TENTATIVES_MAX_COULEUR . " tentatives."
        );
    }

    /**
     * Récupère une catégorie par son identifiant ou lève une exception si
     * elle n'existe pas — évite de dupliquer cette vérification dans
     * chaque méthode publique de ce service.
     *
     * @param int $id Identifiant de la catégorie recherchée
     * @return Categorie La catégorie trouvée
     *
     * @throws \InvalidArgumentException si aucune catégorie ne correspond
     */
    private function trouverOuLever(int $id): Categorie
    {
        $categorie = $this->categorieRepository->trouverParId($id);

        if ($categorie === null) {
            throw new \InvalidArgumentException("Catégorie introuvable avec l'id {$id}.");
        }

        return $categorie;
    }
}