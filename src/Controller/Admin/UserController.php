<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class UserController extends AbstractController
{
    /**
     * UserController constructor.
     *
     * @param UserPasswordHasherInterface $passwordHasher
     * @param EntityManagerInterface $entityManager
     * @param UserRepository $userRepository
     */
    public function __construct(
        protected readonly UserPasswordHasherInterface $passwordHasher,
        protected readonly EntityManagerInterface $entityManager,
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
     * Add user
     *
     * @param Request $request
     *
     * @return Response
     */
    #[Route('/admin/user/add', name: 'admin_user_add', methods: ['GET', 'POST'])]
    public function add(Request $request): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $user->getPassword();

            if ($plainPassword !== null && $plainPassword !== '') {
                $password = $this->passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($password);
            }

            $user->setRoles(['ROLE_USER']);
            $user->setAdmin(false);
            $user->setActive(true);

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            return $this->redirectToRoute('admin_user_index');
        }

        return $this->render('admin/user/add.html.twig', ['form' => $form->createView()]);
    }

    /**
     * Update user
     *
     * @param User $user
     *
     * @return Response
     */
    #[Route('/admin/user/{user}/edit', name: 'admin_user_update', methods: ['GET', 'POST'])]
    public function update(User $user): Response
    {
        return $this->render(
            'admin/user/edit.html.twig',
            ['users' => $user]
        );
    }

    /**
     * Toggle user access
     *
     * @param User $user
     * @return Response
     */
    #[Route('/admin/user/toggle-access/{user}', name: 'admin_user_toggleAccess', methods: ['POST'])]
    public function toggleAccess(User $user): Response
    {
        $user->setActive(!$user->isActive());
        $this->entityManager->flush();

        return $this->redirectToRoute('admin_user_index');
    }

    /**
     * Delete user
     *
     * @param User $user
     * @return Response
     */
    #[Route('/admin/user/delete/{user}', name: 'admin_user_delete')]
    public function delete(User $user): Response
    {
        $this->userRepository->delete($user);

        return $this->redirectToRoute('admin_user_index');
    }
}
