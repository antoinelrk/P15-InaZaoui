<?php

namespace App\Tests\Functional\Controller\Admin;

use App\Entity\Album;
use App\Entity\User;
use App\Tests\TestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Repository\AlbumRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class AlbumControllerTest extends TestCase
{
    /** Simulated HTTP client */
    private KernelBrowser $client;

    /** @var EntityRepository<Album> Album repository */
    private EntityRepository $albumRepository;

    /**
     * Setup before each test:
     * - Initialize the client
     * - Retrieve the EntityManager and Album repository
     * - Ensure repository type consistency
     * - Load and log in an admin user
     */
    protected function setUp(): void
    {
        $this->client = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->client->getContainer()->get('doctrine.orm.entity_manager');

        $this->albumRepository = $entityManager->getRepository(Album::class);

        // Ensure the repository is the expected AlbumRepository
        $this->assertInstanceOf(AlbumRepository::class, $this->albumRepository);

        // Retrieve admin user for authentication
        $adminUser = $entityManager->getRepository(User::class)->findOneBy([
            'email' => 'ina@zaoui.com'
        ]);

        $this->assertNotNull(
            $adminUser,
            'The admin user does not exist in the test database.'
        );

        // Log in as admin user
        $this->client->loginUser($adminUser);
    }

    /**
     * Ensure the index page /admin/album is accessible.
     */
    public function testIndex(): void
    {
        $this->client->request('GET', '/admin/album');
        $this->assertResponseIsSuccessful();
    }

    /**
     * Test album creation:
     * - Access the creation form
     * - Submit the form
     * - Assert redirection after creation
     *
     * @return void
     */
    public function testAddAlbum(): void
    {
        $this->client->request('GET', '/admin/album/add');
        $this->assertResponseIsSuccessful();

        $this->client->submitForm('Ajouter', [
            'album[name]' => 'Test Album',
        ]);

        $this->assertResponseRedirects('/admin/album');
    }

    /**
     * Test updating an existing album:
     * - Find the album named "Test Album"
     * - Access the update form
     * - Submit updated values
     * - Ensure redirection after update
     *
     * @return void
     */
    public function testUpdateAlbum(): void
    {
        $album = $this->albumRepository->findOneBy(['name' => 'Test Album']);

        $this->assertNotNull(
            $album,
            'The album "Test Album" was not found in the test database.'
        );

        $this->client->request('GET', '/admin/album/update/' . $album->id);
        $this->assertResponseIsSuccessful();

        $this->client->submitForm('Modifier', [
            'album[name]' => 'Updated Album'
        ]);

        $this->assertResponseRedirects('/admin/album');
    }

    /**
     * Test album deletion:
     * - Retrieve the album "Updated Album"
     * - Call the delete route
     * - Verify redirection
     *
     * @return void
     */
    public function testDeleteAlbum(): void
    {
        $album = $this->albumRepository->findOneBy(['name' => 'Updated Album']);

        $this->assertNotNull(
            $album,
            'The album "Updated Album" was not found in the test database.'
        );

        $this->client->request('GET', '/admin/album/delete/' . $album->id);
        $this->assertResponseRedirects('/admin/album');
    }
}
