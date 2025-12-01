<?php

namespace App\Controller;

use App\Repository\AlbumRepository;
use App\Repository\MediaRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    /**
     * Number of media per page
     * @var int
     */
    private const int MEDIA_PER_PAGE = 6;

    /**
     * HomeController constructor.
     *
     * @param UserRepository $userRepository
     * @param AlbumRepository $albumRepository
     * @param MediaRepository $mediaRepository
     */
    public function __construct(
        protected readonly UserRepository $userRepository,
        protected readonly AlbumRepository $albumRepository,
        protected readonly MediaRepository $mediaRepository
    ) {}

    /**
     * Display home page
     *
     * @return Response
     */
    #[Route('/', name: 'home')]
    public function home(): Response
    {
        return $this->render('front/home.html.twig');
    }

    /**
     * Display guests page
     *
     * @return Response
     */
    #[Route('/guests', name: 'guests')]
    public function guests(): Response
    {
        $guests = $this->userRepository->findBy(['admin' => false]);

        return $this->render('front/guests.html.twig', [
            'guests' => $guests
        ]);
    }

    /**
     * Display guest page
     *
     * @param int $id
     *
     * @return Response
     */
    #[Route('/guest/{id}', name: 'guest')]
    public function guest(int $id): Response
    {
        $guest = $this->userRepository->find($id);

        return $this->render('front/guest.html.twig', [
            'guest' => $guest
        ]);
    }

    /**
     * Display portfolio page
     *
     * @param Request $request
     * @param int|null $id
     * @return Response
     */
    #[Route('/portfolio/{id}', name: 'portfolio', defaults: ['$id' => null])]
    public function portfolio(Request $request, ?int $id = null): Response
    {
        $albumId = $id;
        $page  = $request->query->getInt('page', 1);

        $user = $this->userRepository->admin();
        $albums = $this->albumRepository->findAll();
        $album = null;

        if ($albumId !== null) {
            $album = current(array_filter($albums, fn ($a) => $a->id === $albumId)) ?: null;
        }

        if ($album) {
            // Display medias from the selected album
            $medias = $this->mediaRepository->get([
                'page' => $page,
                'limit' => self::MEDIA_PER_PAGE,
                'album' => $album,
            ]);
        } else {
            // Display all medias (For admin user)
            $medias = $this->mediaRepository->get([
                'page' => $page,
                'limit' => self::MEDIA_PER_PAGE,
                'user' => $user,
            ]);
        }

        return $this->render('front/portfolio.html.twig', [
            'albums' => $albums,
            'album' => $album,
            'medias' => $medias
        ]);
    }

    /**
     * Display about page
     *
     * @return Response
     */
    #[Route('/about', name: 'about')]
    public function about(): Response
    {
        return $this->render('front/about.html.twig');
    }
}
