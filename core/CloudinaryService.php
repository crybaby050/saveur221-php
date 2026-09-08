<?php

declare(strict_types=1);

namespace Core;

use Cloudinary\Cloudinary;

/*
 * Encapsule l'upload d'images vers Cloudinary. Centralisé ici plutôt que
 * d'appeler le SDK directement dans les services métier (CategorieService,
 * ProduitService) : si un jour le fournisseur de stockage change, seul ce
 * fichier est à modifier.
 */
final class CloudinaryService
{
    private static ?Cloudinary $instance = null;

    private static function client(): Cloudinary
    {
        if (self::$instance === null) {
            self::$instance = new Cloudinary([
                'cloud' => [
                    'cloud_name' => Env::get('CLOUDINARY_CLOUD_NAME'),
                    'api_key' => Env::get('CLOUDINARY_API_KEY'),
                    'api_secret' => Env::get('CLOUDINARY_API_SECRET'),
                ],
            ]);
        }

        return self::$instance;
    }

    /**
     * Envoie un fichier uploadé (via un formulaire HTML classique,
     * $_FILES) vers Cloudinary et retourne l'URL sécurisée de l'image
     * stockée. Le dossier permet de ranger les images par domaine
     * (ex: "categories", "produits") plutôt que de tout mélanger.
     *
     * @param array  $fichier Élément de $_FILES (ex: $_FILES['image'])
     * @param string $dossier Dossier Cloudinary de destination
     * @return string URL sécurisée (https) de l'image uploadée
     *
     * @throws \RuntimeException si l'upload échoue
     */
    public static function uploader(array $fichier, string $dossier): string
    {
        if ($fichier['error'] !== UPLOAD_ERR_OK) {
            throw new \RuntimeException("Erreur lors de l'upload du fichier (code {$fichier['error']}).");
        }

        try {
            $resultat = self::client()->uploadApi()->upload($fichier['tmp_name'], [
                'folder' => "saveur221/{$dossier}",
            ]);

            return $resultat['secure_url'];
        } catch (\Throwable $exception) {
            throw new \RuntimeException("Échec de l'upload vers Cloudinary : " . $exception->getMessage(), previous: $exception);
        }
    }
}