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

        $path = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        if (!is_dir($path) && !mkdir($path, 0775, true) && !is_dir($path)) {
            return false;
        }

        $basename = $filename
            ? pathinfo($filename, PATHINFO_FILENAME)
            : bin2hex(random_bytes(16));

        $webpFilename = $basename . '.webp';
        $webpFullPath = $path . $webpFilename;

        try {
            Image::load($file->getPathname())
                ->format('webp')
                ->quality(80)
                ->save($webpFullPath);

            $media->setPath($webpFullPath);

            $this->entityManager->persist($media);
            $this->entityManager->flush();

            return $media;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Remove media file
     *
     * @param Media $media
     *
     * @return void
     */
    public function remove(Media $media): void
    {
        $filePath = $media->getPath();
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $this->entityManager->remove($media);
        $this->entityManager->flush();
    }
}
