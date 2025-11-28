<?php

namespace App\Controller;

use App\Repository\AlbumRepository;
use App\Repository\MediaRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    /**
     * HomeController constructor.
     *
     * @param UserRepository $userRepository
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
     * @param int|null $id Id of the album to display
     *
     * @return Response
     */
    #[Route('/portfolio/{id}', name: 'portfolio', defaults: ['id' => null])]
    public function portfolio(?int $id = null): Response
    {
        $user = $this->userRepository->admin();
        $albums = $this->albumRepository->findAll();
        $album = null;

        if ($id) {
            $album = current(array_filter($albums, fn ($a) => $a->id === $id)) ?: null;
        }

        if ($album) {
            $medias = $this->mediaRepository->findByAlbum($album->id);
        } else {
            $medias = $this->mediaRepository->findByUser($user->id);
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
