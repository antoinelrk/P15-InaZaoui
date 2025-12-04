<?php

namespace App\Service;

use App\Entity\Media;
use Doctrine\ORM\EntityManagerInterface;
use Random\RandomException;
use Spatie\Image\Image;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final readonly class MediaService
{
    /**
     * MediaService constructor.
     */
    public function __construct(
        protected EntityManagerInterface $entityManager,
    ) {}

    /**
     * Store media file
     *
     * @param Media $media
     * @param string $path
     * @param string|null $filename
     *
     * @return Media|bool
     *
     * @throws RandomException
     */
    public function put(Media $media, string $path, ?string $filename = null): Media|bool
    {
        /** @var UploadedFile|null $file */
        $file = $media->getFile();
        if (!$file instanceof UploadedFile) {
            return false;
        }

        // Normalise le chemin du dossier
        $path = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        if (!is_dir($path) && !mkdir($path, 0775, true) && !is_dir($path)) {
            // impossible de créer le dossier
            return false;
        }

        // Nom de fichier SANS extension
        $basename = $filename
            ? pathinfo($filename, PATHINFO_FILENAME)
            : bin2hex(random_bytes(16));

        // Fichier final .webp
        $webpFilename = $basename . '.webp';
        $webpFullPath = $path . $webpFilename;

        try {
            // Conversion JPG/PNG/… -> WEBP + compression
            Image::load($file->getPathname())
                ->format('webp')   // => pas besoin de Manipulations
                ->quality(80)      // compression
                ->save($webpFullPath);

            // Ici, à toi de décider ce que tu stockes en BDD:
            // - soit juste le nom
            // - soit un chemin relatif type "uploads/xxx.webp"
            $media->setPath($webpFilename);

            $this->entityManager->persist($media);
            $this->entityManager->flush();

            return $media;
        } catch (\Throwable $e) {
            // Tu peux logger si tu veux
            // $this->logger->error('Erreur upload image', ['exception' => $e]);
            return false;
        }
    }
}
