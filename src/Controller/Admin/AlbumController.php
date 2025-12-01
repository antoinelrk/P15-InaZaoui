<?php

namespace App\Controller\Admin;

use App\Entity\Album;
use App\Entity\Media;
use App\Form\AlbumType;
use App\Form\MediaType;
use App\Repository\AlbumRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AlbumController extends AbstractController
{
    /**
     * AlbumController constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param AlbumRepository $albumRepository
     */
    public function __construct(
        protected readonly EntityManagerInterface $entityManager,
        protected readonly AlbumRepository $albumRepository,
    ) {}

    /**
     * List albums
     *
     * @return Response
     */
    #[Route("/admin/album", name: "admin_album_index")]
    public function index(): Response
    {
        $albums = $this->albumRepository->findAll();

        return $this->render('admin/album/index.html.twig', ['albums' => $albums]);
    }

    /**
     * Add album
     *
     * @param Request $request
     *
     * @return Response
     */
    #[Route("/admin/album/add", name: "admin_album_add")]
    public function add(Request $request): Response
    {
        $album = new Album();
        $form = $this->createForm(AlbumType::class, $album);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($album);
            $this->entityManager->flush();

            return $this->redirectToRoute('admin_album_index');
        }

        return $this->render('admin/album/add.html.twig', ['form' => $form->createView()]);
    }

    /**
     * Update album
     *
     * @param Request $request
     * @param int $id
     *
     * @return Response
     */
    #[Route("/admin/album/update/{id}", name: "admin_album_update")]
    public function update(Request $request, int $id): Response
    {
        $album = $this->albumRepository->find($id);
        $form = $this->createForm(AlbumType::class, $album);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('admin_album_index');
        }

        return $this->render('admin/album/update.html.twig', ['form' => $form->createView()]);
    }

    /**
     * Delete album
     *
     * @param int $id
     *
     * @return Response
     */
    #[Route("/admin/album/delete/{id}", name: "admin_album_delete")]
    public function delete(int $id): Response
    {
        $media = $this->albumRepository->find($id);
        $this->entityManager->remove($media);
        $this->entityManager->flush();

        return $this->redirectToRoute('admin_album_index');
    }
}
