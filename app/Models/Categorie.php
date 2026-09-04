<?php

declare(strict_types=1);

namespace App\Models;

/*
 * Représente une catégorie de produits. image et couleur sont gérées
 * exclusivement par ce module PHP (contrairement au Java Console, qui les
 * lit mais ne les écrit jamais) : image est l'URL Cloudinary de
 * l'illustration, couleur un code hexadécimal unique servant d'identité
 * visuelle à la catégorie dans l'interface.
 */
final class Categorie
{
    public function __construct(
        private int $id,
        private string $nom,
        private ?string $description,
        private ?string $image = null,
        private ?string $couleur = null,
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function setNom(string $nom): void
    {
        $this->nom = $nom;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): void
    {
        $this->image = $image;
    }

    public function getCouleur(): ?string
    {
        return $this->couleur;
    }

    public function setCouleur(?string $couleur): void
    {
        $this->couleur = $couleur;
    }

    /*
     * Reconstruit une instance à partir d'une ligne de résultat PDO —
     * centralise ici le mapping colonne -> propriété, pour que
     * CategorieRepository n'ait pas à connaître le détail des noms de
     * colonnes SQL en dehors de sa propre requête.
     */
    public static function depuisLigne(array $ligne): self
    {
        return new self(
            id: (int) $ligne['id'],
            nom: $ligne['nom'],
            description: $ligne['description'],
            image: $ligne['image'],
            couleur: $ligne['couleur'],
        );
    }
}