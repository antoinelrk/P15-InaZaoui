<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Controller\HomeController;
use App\Entity\Album;
use App\Entity\User;
use App\Repository\AlbumRepository;
use App\Repository\MediaRepository;
use App\Repository\UserRepository;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

#[CoversClass(HomeController::class)]
final class HomeControllerTest extends WebTestCase
{
    private function createClientWithRepositories(
        ?UserRepository  $userRepository = null,
        ?AlbumRepository $albumRepository = null,
        ?MediaRepository $mediaRepository = null,
    ): KernelBrowser
    {
        $client = static::createClient();
        $container = static::getContainer();

        if ($userRepository !== null) {
            $container->set(UserRepository::class, $userRepository);
        }

        if ($albumRepository !== null) {
            $container->set(AlbumRepository::class, $albumRepository);
        }

        if ($mediaRepository !== null) {
            $container->set(MediaRepository::class, $mediaRepository);
        }

        return $client;
    }

    #[Test]
    public function home_page_is_successful(): void
    {
        $client = static::createClient();

        $client->request('GET', '/');

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    #[Test]
    public function about_page_is_successful(): void
    {
        $client = static::createClient();

        $client->request('GET', '/about');

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    #[Test]
    public function guests_page_uses_user_repository_and_is_successful(): void
    {
        $userRepository = $this->createMock(UserRepository::class);
        $userRepository
            ->expects(self::once())
            ->method('findBy')
            ->with(['admin' => false])
            ->willReturn([]); // Peu importe le contenu, on teste l'appel

        $client = $this->createClientWithRepositories(
            userRepository: $userRepository,
        );

        $client->request('GET', '/guests');

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    #[Test]
    public function guest_page_loads_guest_by_id(): void
    {
        $guestId = 42;

        $guest = new User();
        $guest->id = $guestId;
        $guest->setName('John Doe');
        $guest->setDescription('Guest description');

        $userRepository = $this->createMock(UserRepository::class);
        $userRepository
            ->expects(self::once())
            ->method('find')
            ->with($guestId)
            ->willReturn($guest);

        $client = $this->createClientWithRepositories(
            userRepository: $userRepository,
        );

        $client->request('GET', '/guest/' . $guestId);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    #[Test]
    public function portfolio_without_album_id_fetches_medias_for_admin_user(): void
    {
        $page = 1;
        $mediaPerPage = 6;

        // admin() doit renvoyer un User (ou mock)
        $adminUser = $this->createMock(User::class);

        // Albums utilisés par le template : a.id et a.name
        $album1 = new \stdClass();
        $album1->id = 10;
        $album1->name = 'Album 1';

        $album2 = new \stdClass();
        $album2->id = 20;
        $album2->name = 'Album 2';

        $userRepository = $this->createMock(UserRepository::class);
        $userRepository
            ->expects(self::once())
            ->method('admin')
            ->willReturn($adminUser);

        $albumRepository = $this->createMock(AlbumRepository::class);
        $albumRepository
            ->expects(self::once())
            ->method('findAll')
            ->willReturn([$album1, $album2]);

        $mediaRepository = $this->createMock(MediaRepository::class);

        // MediaRepository::get doit renvoyer un Pagerfanta
        $pagerfanta = new Pagerfanta(new ArrayAdapter([]));

        $mediaRepository
            ->expects(self::once())
            ->method('get')
            ->with(self::callback(function (array $criteria) use ($page, $mediaPerPage, $adminUser): bool {
                self::assertSame($page, $criteria['page'] ?? null);
                self::assertSame($mediaPerPage, $criteria['limit'] ?? null);
                self::assertSame($adminUser, $criteria['user'] ?? null);
                self::assertTrue($criteria['active_user'] ?? false);

                return true; // important pour satisfaire le matcher
            }))
            ->willReturn($pagerfanta);

        $client = $this->createClientWithRepositories(
            userRepository: $userRepository,
            albumRepository: $albumRepository,
            mediaRepository: $mediaRepository,
        );

        $client->request('GET', '/portfolio');

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    #[Test]
    public function portfolio_with_album_id_fetches_medias_for_that_album(): void
    {
        $page = 2;
        $mediaPerPage = 6;
        $requestedAlbumId = 20;

        // admin() doit renvoyer un User (ou mock)
        $adminUser = $this->createMock(User::class);

        // Albums : doivent avoir id + name pour le Twig
        $album1 = new \stdClass();
        $album1->id = 10;
        $album1->name = 'Album 1';

        $album2 = new \stdClass();
        $album2->id = $requestedAlbumId;
        $album2->name = 'Album 2';

        $userRepository = $this->createMock(UserRepository::class);
        $userRepository
            ->expects(self::once())
            ->method('admin')
            ->willReturn($adminUser);

        $albumRepository = $this->createMock(AlbumRepository::class);
        $albumRepository
            ->expects(self::once())
            ->method('findAll')
            ->willReturn([$album1, $album2]);

        $mediaRepository = $this->createMock(MediaRepository::class);

        // get() doit renvoyer un Pagerfanta
        $pagerfanta = new Pagerfanta(new ArrayAdapter([]));

        $mediaRepository
            ->expects(self::once())
            ->method('get')
            ->with(self::callback(function (array $criteria) use ($page, $mediaPerPage, $album2): bool {
                self::assertSame($page, $criteria['page'] ?? null);
                self::assertSame($mediaPerPage, $criteria['limit'] ?? null);
                self::assertSame($album2, $criteria['album'] ?? null);
                self::assertTrue($criteria['active_user'] ?? false);

                // optionnel : on s'assure qu'on n'est plus dans la branche "user"
                self::assertArrayNotHasKey('user', $criteria);

                return true; // IMPORTANT pour satisfaire le matcher
            }))
            ->willReturn($pagerfanta);

        $client = $this->createClientWithRepositories(
            userRepository: $userRepository,
            albumRepository: $albumRepository,
            mediaRepository: $mediaRepository,
        );

        $client->request('GET', '/portfolio/' . $requestedAlbumId . '?page=' . $page);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
    }

}
