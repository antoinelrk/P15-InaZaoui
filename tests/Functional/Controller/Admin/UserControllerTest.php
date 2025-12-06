<?php

namespace App\Tests\Functional\Controller\Admin;

use App\Entity\User;
use App\Tests\TestCase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

class UserControllerTest extends TestCase
{
    /** HTTP test client */
    private KernelBrowser $client;

    /** Initialize the test client before each test */
    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    /**
     * Retrieve the administrator user used for secured routes.
     */
    private function getAdminUser(): ?User
    {
        return $this->getEntityManager()
            ->getRepository(User::class)
            ->findOneBy(['email' => 'ina@zaoui.com']);
    }

    /**
     * Retrieve a regular user for toggle-access tests.
     */
    private function getUser(): ?User
    {
        return $this->getEntityManager()
            ->getRepository(User::class)
            ->findOneBy(['email' => 'john@doe.fr']);
    }

    /**
     * Shortcut to retrieve the Doctrine EntityManager.
     */
    private function getEntityManager(): EntityManagerInterface
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->client
            ->getContainer()
            ->get('doctrine.orm.entity_manager');

        return $entityManager;
    }

    /**
     * Test accessing the users index page as an authenticated admin user.
     */
    public function testIndex(): void
    {
        $adminUser = $this->getAdminUser();
        $this->assertNotNull($adminUser, 'L\'utilisateur admin n\'existe pas.');

        $this->client->loginUser($adminUser);
        $this->client->request('GET', '/admin/user');

        $this->assertResponseIsSuccessful();
    }

    /**
     * Test adding a new user:
     * - Access the "add" form
     * - Submit the form with a unique email
     * - Ensure redirection and persistence in the database
     */
    public function testAddUser(): void
    {
        $adminUser = $this->getAdminUser();
        $this->assertNotNull($adminUser, 'L\'utilisateur admin n\'existe pas.');

        $this->client->loginUser($adminUser);

        $this->client->request('GET', '/admin/user/add');
        $this->assertResponseIsSuccessful();

        // Generate a unique email for each test execution
        $email = 'roger+' . uniqid('', true) . '@saucisse.com';

        // Submit creation form
        $this->client->submitForm('Ajouter', [
            'user[name]' => 'Roger Saucisse',
            'user[email]' => $email,
            'user[password][first]' => 'password',
            'user[password][second]' => 'password',
            'user[description]' => 'Je suis une saucisse.',
        ]);

        // Expect redirect after successful creation
        $this->assertResponseRedirects('/admin/user');

        // Follow redirect to ensure the process completes
        $this->client->followRedirect();

        $entityManager = $this->getEntityManager();
        $userRepo = $entityManager->getRepository(User::class);

        // Validate newly created user
        $newUser = $userRepo->findOneBy(['email' => $email]);

        $this->assertNotNull($newUser, 'The new user was not created.');
        $this->assertEquals('Roger Saucisse', $newUser->getName());
        $this->assertEquals($email, $newUser->getEmail());
    }

    /**
     * Test toggling the access state ("active" flag) of an existing user.
     */
    public function testToggleAccessUser(): void
    {
        $admin = $this->getAdminUser();
        $this->assertNotNull($admin);

        $this->client->loginUser($admin);

        $user = $this->getUser();
        $this->assertNotNull($user);

        // Store the initial state to compare after toggle
        $initialAccess = $user->isActive();

        // Trigger access toggle
        $this->client->request('POST', '/admin/user/toggle-access/' . $user->id);
        $this->assertResponseRedirects('/admin/user');

        // Refresh user entity to fetch updated state
        $this->getEntityManager()->refresh($user);

        // Assert the "active" value has been inverted
        $this->assertEquals(!$initialAccess, $user->isActive());
    }

    /**
     * Test deleting a user:
     * - Create a temporary user
     * - Delete it via the controller
     * - Ensure it no longer exists in the database
     */
    public function testDeleteUser(): void
    {
        $adminUser = $this->getAdminUser();
        $this->assertNotNull($adminUser);

        $this->client->loginUser($adminUser);

        $entityManager = $this->getEntityManager();
        $userRepo = $entityManager->getRepository(User::class);

        // Generate unique email for temporary user
        $email = 'roberto+' . uniqid('', true) . '@saucisse.com';

        // Create a user to be deleted
        $user = new User();
        $user->setName('Roberto Saucisse');
        $user->setEmail($email);
        $user->setPassword('password');
        $user->setRoles(['ROLE_USER']);

        $entityManager->persist($user);
        $entityManager->flush();

        // Ensure user was persisted
        $this->assertNotNull(
            $userRepo->findOneBy(['email' => $email]),
            'Temporary user was not created.'
        );

        // Trigger deletion
        $this->client->request('DELETE', '/admin/user/delete/' . $user->id);

        $this->assertResponseRedirects('/admin/user');

        // Validate deletion
        $deletedUser = $userRepo->findOneBy(['email' => $email]);
        $this->assertNull($deletedUser, 'User was not deleted.');
    }
}
