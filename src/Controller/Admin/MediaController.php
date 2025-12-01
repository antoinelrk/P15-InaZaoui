<?php

namespace App\Controller\Admin;

use App\Entity\Media;
use App\Form\MediaType;
use App\Repository\MediaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MediaController extends AbstractController
{
    /**
     * Number of media per page
     * @var int
     */
    private const int MEDIA_PER_PAGE = 15;

    /**
     * MediaController constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param MediaRepository $mediaRepository
     */
    public function __construct(
        protected readonly EntityManagerInterface $entityManager,
        protected readonly MediaRepository $mediaRepository,
    ) {}

    /**
     * List medias
     *
     * @param Request $request
     *
     * @return Response
     */
    #[Route("/admin/media", name: "admin_media_index")]
    public function index(Request $request): Response
    {
        $page = $request->query->getInt('page', 1);

        $filters = [];

        if (!$this->isGranted('ROLE_ADMIN')) {
            $filters = [
                'user' => $this->getUser(),
            ];
        }

        $medias = $this->mediaRepository->get([
            ...$filters,
            'page' => $page,
            'limit' => self::MEDIA_PER_PAGE,
        ]);

        $total = $medias->getNbResults();

        return $this->render('admin/media/index.html.twig', [
            'medias' => $medias,
            'total' => $total,
            'page' => $page
        ]);
    }

    /**
     * Add a media
     *
     * @param Request $request
     *
     * @return Response
     */
    #[Route("/admin/media/add", name: "admin_media_add")]
    public function add(Request $request): Response
    {
        $media = new Media();
        $form = $this->createForm(MediaType::class, $media, ['is_admin' => $this->isGranted('ROLE_ADMIN')]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$this->isGranted('ROLE_ADMIN')) {
                $media->setUser($this->getUser());
            }

            // TODO: ajouter un formateur de nom de fichier et gérer le poids des images.
            $media->setPath('uploads/' . md5(uniqid()) . '.' . $media->getFile()->guessExtension());
            $media->getFile()->move('uploads/', $media->getPath());

            $this->entityManager->persist($media);
            $this->entityManager->flush();

            return $this->redirectToRoute('admin_media_index');
        }

        return $this->render('admin/media/add.html.twig', ['form' => $form->createView()]);
    }

    /**
     * Delete a media
     *
     * @param int $id
     * @return Response
     */
    #[Route("/admin/media/delete/{id}", name: "admin_media_delete")]
    public function delete(int $id): Response
    {
        $media = $this->mediaRepository->find($id);
        $this->entityManager->remove($media);
        $this->entityManager->flush();
        unlink($media->getPath());

        return $this->redirectToRoute('admin_media_index');
    }
}
