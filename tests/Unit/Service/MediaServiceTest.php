<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Entity\Media;
use App\Service\MediaService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[CoversClass(MediaService::class)]
final class MediaServiceTest extends TestCase
{
    /** Mocked EntityManager */
    private EntityManagerInterface $entityManager;

    /** Service under test */
    private MediaService $service;

    /**
     * Initialize the mocked EntityManager and the MediaService.
     */
    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->service = new MediaService($this->entityManager);
    }

    /**
     * put() should return false if the Media entity has no file assigned.
     * No database write should be performed.
     */
    #[Test]
    public function put_returnsFalse_whenMediaHasNoFile(): void
    {
        $media = new Media();

        // Ensure no database write occurs
        $this->entityManager
            ->expects(self::never())
            ->method('persist');

        $this->entityManager
            ->expects(self::never())
            ->method('flush');

        $result = $this->service->put(
            $media,
            sys_get_temp_dir() . '/media_service'
        );

        self::assertFalse($result);
    }

    /**
     * put() should:
     * - Convert the image to WebP
     * - Persist the media entity
     * - Update its path with the expected WebP filename
     */
    #[Test]
    public function put_createsWebpFile_andPersistsMedia_whenEverythingIsValid(): void
    {
        $media = new Media();

        // Create a temporary JPEG image (requires GD)
        $sourcePath = sys_get_temp_dir() . '/media_source_' . uniqid('', true) . '.jpg';
        $image = imagecreatetruecolor(1, 1);
        imagejpeg($image, $sourcePath);
        imagedestroy($image);

        // Uploaded file used by the service
        $uploadedFile = new UploadedFile(
            $sourcePath,
            'original.jpg',
            'image/jpeg',
            0,
            true // "test" mode — avoids HTTP upload checks
        );

        $media->setFile($uploadedFile);

        // Dedicated temp directory for the generated WebP file
        $targetDir = sys_get_temp_dir() . '/media_target_' . uniqid('', true);

        // Database write expectations
        $this->entityManager
            ->expects(self::once())
            ->method('persist')
            ->with(self::identicalTo($media));

        $this->entityManager
            ->expects(self::once())
            ->method('flush');

        // Force a specific output filename to test against
        $result = $this->service->put($media, $targetDir, 'custom-name.jpg');

        // Should return the Media instance
        self::assertSame($media, $result);

        $expectedDir  = rtrim($targetDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $expectedPath = $expectedDir . 'custom-name.webp';

        // Service must have updated the persisted path
        self::assertSame($expectedPath, $media->getPath());
        self::assertFileExists($expectedPath);

        // Cleanup
        @unlink($expectedPath);
        @unlink($sourcePath);
        @rmdir($targetDir);
    }

    /**
     * remove() should delete the physical file on disk
     * and remove the corresponding Media entity from the database.
     */
    #[Test]
    public function remove_deletesPhysicalFile_andRemovesEntity(): void
    {
        // Create a dummy temp file
        $tempFile = tempnam(sys_get_temp_dir(), 'media_to_remove_');
        file_put_contents($tempFile, 'dummy-content');

        $media = new Media();
        $media->setPath($tempFile);

        // Ensure EntityManager::remove() + flush() are called
        $this->entityManager
            ->expects(self::once())
            ->method('remove')
            ->with(self::identicalTo($media));

        $this->entityManager
            ->expects(self::once())
            ->method('flush');

        // Execute removal
        $this->service->remove($media);

        // The file must no longer exist
        self::assertFileDoesNotExist($tempFile);
    }
}
