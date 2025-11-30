<?php

namespace App\DataFixtures;

use App\Entity\Album;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class AlbumFixtures extends Fixture
{
    /**
     * Load album fixtures into the database
     *
     * @param ObjectManager $manager
     * @return void
     */
    public function load(ObjectManager $manager): void
    {
        $albumsNames = [
            'Voyage',
            'Nature',
            'Portraits',
            'Événements',
            'Mariage',
            'Luxe',
        ];

        foreach ($albumsNames as $name) {
            $album = new Album;
            $album->setName($name);
            $manager->persist($album);
        }

        $manager->flush();
    }
}
