<?php

namespace App\Tests\Functional\Controller\Admin;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $this->entityManager = $entityManager;
    }

    public function testLoginPage(): void
    {
        $this->client->request('GET', '/login');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Connexion');
    }

    public function testLogOut(): void
    {
        $this->client->request('GET', '/logout');
        self::assertResponseRedirects('/');
        $this->client->followRedirect();
        self::assertResponseIsSuccessful();
    }

    public function testLogoutRedirectsUser(): void
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy([
            'active' => false,
        ]);
        self::assertNotNull($user);

        $this->client->loginUser($user);
        $this->client->request('GET', '/logout');
        self::assertResponseRedirects('/');
        $this->client->followRedirect();

        self::assertSelectorExists('nav a[href="/login"]');
    }

    public function testIncorrectUsername(): void
    {
        $crawler = $this->client->request('GET', '/login');

        $form = $crawler->selectButton('Connexion')->form([
            '_username' => 'incorrectUsername',
            '_password' => 'password',
        ]);

        $this->client->submit($form);
        self::assertResponseRedirects('/login');
        $this->client->followRedirect();

        self::assertSelectorTextContains('div.alert-danger', 'Invalid credentials.');
    }

    public function testIncorrectPassword(): void
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy([
            'active' => false,
        ]);
        self::assertNotNull($user);

        $crawler = $this->client->request('GET', '/login');

        $form = $crawler->selectButton('Connexion')->form([
            '_username' => $user->getName(),
            '_password' => 'incorrectPassword',
        ]);

        $this->client->submit($form);
        self::assertResponseRedirects('/login');
        $this->client->followRedirect();

        self::assertSelectorTextContains('div.alert-danger', 'Invalid credentials.');
    }

    public function testRestrictedUserCannotLogin(): void
    {
        $restrictedUser = $this->entityManager->getRepository(User::class)->findOneBy([
            'active' => true,
        ]);
        self::assertNotNull($restrictedUser, 'No restricted user found.');

        $crawler = $this->client->request('GET', '/login');

        $form = $crawler->selectButton('Connexion')->form([
            '_username' => $restrictedUser->getName(),
            '_password' => 'password',
        ]);

        $this->client->submit($form);
        self::assertResponseRedirects('/login');
        $this->client->followRedirect();

        self::assertSelectorTextContains('div.alert-danger', 'Invalid credentials.');
    }

    protected function tearDown(): void
    {
        $this->entityManager->close();

        parent::tearDown();
    }
}
