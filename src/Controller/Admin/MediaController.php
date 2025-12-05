<?php

namespace App\Controller\Admin;

use App\Controller\BaseController;
use App\Entity\Media;
use App\Form\MediaType;
use App\Repository\MediaRepository;
use App\Service\MediaService;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Random\RandomException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MediaController extends BaseController
{
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
     * @param MediaService $mediaService
     *
     * @return Response
     *
     * @throws Exception
     * @throws RandomException
     */
    #[Route("/admin/media/add", name: "admin_media_add")]
    public function add(Request $request, MediaService $mediaService): Response
    {
        $media = new Media();
        $form = $this->createForm(MediaType::class, $media, ['is_admin' => $this->isGranted('ROLE_ADMIN')]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$this->isGranted('ROLE_ADMIN')) {
                $media->setUser($this->getUser());
            }

            // Check if a file is selected
            if (!$media->getFile()) {
                $this->addFlash('error', 'Veuillez sélectionner un fichier à uploader.');

                return $this->render('admin/media/add.html.twig', ['form' => $form->createView()]);
            }

            if (!$mediaService->put(media: $media, path: 'uploads/')) {
                $this->addFlash('error', 'Une erreur est survenue lors de l\'upload du fichier.');

                return $this->render('admin/media/add.html.twig', ['form' => $form->createView()]);
            }

            // TODO: FLASH SUCCESS

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
