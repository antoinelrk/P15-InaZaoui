<?php

namespace App\DataFixtures;

use App\Entity\Media;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Finder\Finder;

final class MediaFixtures extends Fixture implements DependentFixtureInterface
{
    private int $numberOfMedias;

    public function __construct()
    {
        $this->numberOfMedias = (new Finder)->files()->in('public/uploads')->count();
    }

    public function load(ObjectManager $manager): void
    {
        $users = $manager->getRepository(User::class)->findAll();

        foreach ($users as $user) {
            if ($user->id === 1) {
                continue;
            }

            for ($i = 1; $i <= 20; $i++) {
                $randomIndex = rand(1, $this->numberOfMedias);

                $media = new Media;

//                $media->setPath('uploads/' . $randomIndex . '.webp');
                $media->setPath('https://placehold.co/400x600');
                $media->setUser($user);
                $media->setTitle("Photo $randomIndex de l'utilisateur {$user->getName()}");

                $manager->persist($media);
            }

        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }
}
