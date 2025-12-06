<?php

namespace App\Tests\Functional\Controller\Admin;

use App\Entity\Media;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MediaControllerTest extends WebTestCase
{
    /** Simulated HTTP client */
    private KernelBrowser $client;

    /** Entity manager used across tests */
    private EntityManagerInterface $entityManager;

    /**
     * Prepare the client and authenticate an admin user before each test:
     * - Boot the kernel and create a client
     * - Retrieve the EntityManager
     * - Fetch an admin user from the database
     * - Log in as this admin user for all requests
     */
    protected function setUp(): void
    {
        $this->client = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $this->entityManager = $this->client
            ->getContainer()
            ->get('doctrine.orm.entity_manager');

        // Fetch an administrator user from the test database
        $adminUser = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => 'ina@zaoui.com']);

        // Ensure the admin user exists in the test database
        $this->assertNotNull(
            $adminUser,
            'The admin user does not exist in the test database.'
        );

        // Log in the admin user for subsequent requests
        $this->client->loginUser($adminUser);
    }

    /**
     * Test that the media index page is accessible.
     */
    public function testIndex(): void
    {
        $this->client->request('GET', '/admin/media');

        $this->assertResponseIsSuccessful();
    }

    /**
     * Test adding a new media:
     * - Access the media creation form
     * - Build a temporary uploaded file
     * - Submit the form with valid data
     * - Assert the redirect
     * - Assert the media is persisted in database
     */
    public function testAddMedia(): void
    {
        // Access the "add media" page
        $this->client->request('GET', '/admin/media/add');
        $this->assertResponseIsSuccessful();

        // Create a temporary copy of an existing file to simulate an upload
        $tempFilePath = tempnam(sys_get_temp_dir(), 'testMedia3') . '.jpg';

        if (!copy('public/images/ina.png', $tempFilePath)) {
            throw new \RuntimeException('Failed to copy the test file for upload.');
        }

        // Build a Symfony UploadedFile instance
        $uploadedFile = new UploadedFile(
            $tempFilePath,
            'test_image.jpg',
            'image/jpeg',
            null,
            true // mark as already moved (test mode)
        );

        // Submit the form with valid media data
        $this->client->submitForm('Ajouter', [
            'media[title]' => 'TestMedia3',
            'media[user]' => '',
            'media[album]' => '',
            'media[file]' => $uploadedFile,
        ]);

        // After a successful submission, we expect a redirect to the index
        $this->assertResponseRedirects('/admin/media');

        // Fetch the newly created media from database
        $media = $this->entityManager
            ->getRepository(Media::class)
            ->findOneBy(['title' => 'TestMedia3']);

        $this->assertNotNull($media, 'The media entity was not created in the database.');
        $this->assertEquals('TestMedia3', $media->getTitle());

        // Clean up the temporary file
        @unlink($tempFilePath);
    }

    /**
     * Test deleting an existing media:
     * - Create a media entity to be deleted
     * - Persist it in the database
     * - Call the delete route
     * - Assert a redirect
     * - Assert the entity has been removed
     */
    public function testDeleteMedia(): void
    {
        // Create a media entity to be deleted during the test
        $media = new Media();
        $media->setTitle('Media to Delete');
        $media->setPath('test_path.txt');

        $this->entityManager->persist($media);
        $this->entityManager->flush();

        $mediaId = $media->getId();
        $this->assertNotNull($mediaId, 'The test media ID was not generated.');

        // Call the delete route for this media
        $this->client->request('GET', '/admin/media/delete/' . $mediaId);
        $this->assertResponseRedirects('/admin/media');

        // Ensure the media has been removed from the database
        $deletedMedia = $this->entityManager
            ->getRepository(Media::class)
            ->find($mediaId);

        $this->assertNull($deletedMedia, 'The media entity was not deleted from the database.');
    }

    /**
     * Clean up after each test:
     * - Remove test media entities created during tests
     * - Flush changes
     * - Call parent::tearDown() to properly shut down the kernel
     */
    protected function tearDown(): void
    {
        // Clean up only test-related media entities
        $mediaRepository = $this->entityManager->getRepository(Media::class);

        $testMedias = $mediaRepository->findBy([
            'title' => ['TestMedia3', 'Media to Delete'],
        ]);

        foreach ($testMedias as $media) {
            $this->entityManager->remove($media);
        }

        $this->entityManager->flush();

        parent::tearDown();
    }
}
