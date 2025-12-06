<?php

namespace App\Tests\Unit\Templates;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Twig\Environment;

class FrontTemplateTest extends KernelTestCase
{
    private Environment $twig;

    protected function setUp(): void
    {
        self::bootKernel();

        /** @var Environment $twig */
        $twig = self::getContainer()->get(Environment::class);
        $this->twig = $twig;
    }

    public function testNavbarForAnonymousUser(): void
    {
        // On rend le template "front.html.twig" sans contexte particulier
        $html = $this->twig->render('front.html.twig');

        // On vérifie que les liens de navigation existent (par leur texte)
        $this->assertStringContainsString('Invités', $html);
        $this->assertStringContainsString('Portfolio', $html);
        $this->assertStringContainsString('Qui suis-je ?', $html);

        // Utilisateur non connecté → on doit voir "Connexion"
        $this->assertStringContainsString('Connexion', $html);

        // Et on ne doit PAS voir "Déconnexion" ni "Administration"
        $this->assertStringNotContainsString('Déconnexion', $html);
        $this->assertStringNotContainsString('Administration', $html);

        // Footer présent
        $this->assertStringContainsString('Ina Zaoui copyright 2024', $html);
    }
}
