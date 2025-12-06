<?php

namespace App\Tests\Unit\Templates;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Twig\Environment;

class FrontTemplateAdminTest extends KernelTestCase
{
    private Environment $twig;
    private TokenStorageInterface $tokenStorage;

    protected function setUp(): void
    {
        self::bootKernel();

        /** @var Environment $twig */
        $twig = self::getContainer()->get(Environment::class);
        $this->twig = $twig;

        /** @var TokenStorageInterface $tokenStorage */
        $tokenStorage = self::getContainer()->get(TokenStorageInterface::class);
        $this->tokenStorage = $tokenStorage;
    }

    private function logInAdminUser(): void
    {
        $user = new User();
        $user->setEmail('admin@example.com');
        $user->setRoles(['ROLE_ADMIN']);

        // Le nom du firewall ("main") doit correspondre à ton config/packages/security.yaml
        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());

        $this->tokenStorage->setToken($token);
    }

    public function testNavbarForAdminUser(): void
    {
        $this->logInAdminUser();

        $html = $this->twig->render('front.html.twig');

        // Pour un admin connecté → "Déconnexion" présent
        $this->assertStringContainsString('Déconnexion', $html);

        // Et le lien "Administration" doit être visible
        $this->assertStringContainsString('Administration', $html);
    }
}
