<?php

namespace App\Tests;

use App\DataFixtures\AlbumFixtures;
use App\DataFixtures\MediaFixtures;
use App\DataFixtures\UserFixtures;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as SymfonyWebTestCase;

abstract class TestCase extends SymfonyWebTestCase
{
    protected AbstractDatabaseTool $databaseTool;

    protected function setUp(): void
    {
        parent::setUp();

        // Client HTTP
        $this->client = static::createClient();

        // Outil Liip pour reset + fixtures
        /** @var DatabaseToolCollection $databaseToolCollection */
        $databaseToolCollection = static::getContainer()->get(DatabaseToolCollection::class);
        $this->databaseTool = $databaseToolCollection->get();

        // Ici tu décides la "base" de tes données pour TOUS les tests fonctionnels
        $this->databaseTool->loadFixtures([
            AlbumFixtures::class,
            UserFixtures::class,
            MediaFixtures::class,
        ]);
    }
}
