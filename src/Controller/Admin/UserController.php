<?php

namespace App\Controller\Admin;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class UserController extends AbstractController
{
    /**
     * UserController constructor.
     *
     * @param UserRepository $userRepository
     */
    public function __construct(
        protected readonly UserRepository $userRepository,
    ) {}

    /**
     * List users
     *
     * @return Response
     */
    #[Route('/admin/user', name: 'admin_user_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('admin/user/index.html.twig', [
            'users' => $this->userRepository->get(),
        ]);
    }

    /**
     * Update user
     *
     * @param int $id
     * @return Response
     */
    #[Route('/admin/user/{id}/edit', name: 'admin_user_update', methods: ['GET', 'POST'])]
    public function update(int $id): Response
    {
        return $this->render('admin/user/update.html.twig', [
            'users' => $this->userRepository->findOneBy(["id" => $id]),
        ]);
    }

    /**
     * Delete user
     *
     * @param int $id
     * @return Response
     */
    #[Route('/admin/user/{id}', name: 'admin_user_delete', methods: ['DELETE'])]
    public function delete(int $id): Response
    {
        $this->userRepository->delete($id);

        return $this->redirectToRoute('admin_user_index');
    }
}
