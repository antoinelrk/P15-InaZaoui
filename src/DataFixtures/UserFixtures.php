<?php

namespace App\DataFixtures;

use App\Entity\Album;
use App\Entity\Media;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class UserFixtures extends Fixture implements DependentFixtureInterface
{
    private const string DEFAULT_PASSWORD = 'password';

    private Generator $faker;

    public function __construct(
        protected readonly UserPasswordHasherInterface $passwordHasher,
    ) {
        $this->faker = Factory::create('fr_FR'); // Localisation FR
    }

    public function load(ObjectManager $manager): void
    {
        $this->setAdmin($manager);
        $this->setUsers($manager);

        $manager->flush();
    }

    /**
     * Set the admin user
     *
     * @param ObjectManager $manager
     * @return void
     */
    private function setAdmin(ObjectManager $manager): void
    {
        $user = new User;

        $user->setName('Ina Zaoui');
        $user->setEmail('ina@zaoui.com');
        $user->setRoles(['ROLE_ADMIN']);
        $user->setPassword($this->passwordHasher->hashPassword($user, self::DEFAULT_PASSWORD));

        $manager->persist($user);

        $albums = $manager->getRepository(Album::class)->findAll();

        for ($i = 1; $i <= 50; $i++) {
            $media = new Media;

//            $media->setPath('uploads/' . $i . '.webp');
            $media->setPath('https://placehold.co/400x600');
            $media->setUser($user);
            $media->setAlbum($albums[$i % count($albums)]);
            $media->setTitle("Photo $i");

            $manager->persist($media);
        }
    }

    /**
     * Set regular users
     *
     * @param ObjectManager $manager
     * @return void
     */
    public function setUsers(ObjectManager $manager): void
    {
        // Create basic user
        $user = new User;
        $user->setName('Jane Doe');
        $user->setEmail('jane@doe.fr');
        $user->setRoles(['ROLE_USER']);
        $user->setPassword($this->passwordHasher->hashPassword($user, self::DEFAULT_PASSWORD));
        $manager->persist($user);

        // Create restricted user
        $user = new User;
        $user->setName('John Doe');
        $user->setEmail('john@doe.fr');
        $user->setRoles(['ROLE_USER']);
        $user->setActive(false);
        $user->setPassword($this->passwordHasher->hashPassword($user, self::DEFAULT_PASSWORD));
        $manager->persist($user);

        for ($i = 0; $i < 9; $i++) {
            $user = new User;

            $user->setName('Invité ' . $i);
            $user->setEmail('invite+' . $i . '@example.com');
            $user->setRoles(['ROLE_USER']);
            $user->setActive($this->faker->boolean(80));
            $user->setPassword($this->passwordHasher->hashPassword($user, self::DEFAULT_PASSWORD));

            $manager->persist($user);
        }
    }

    public function getDependencies(): array
    {
        return [
            AlbumFixtures::class
        ];
    }
}
