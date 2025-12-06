<?php

namespace App\Repository;

use App\Entity\User;
use App\Traits\HasPagination;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Pagerfanta\Pagerfanta;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    use HasPagination;

    public function __construct(protected readonly ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Get paginated users
     *
     * @param array $filters
     *
     * @return Pagerfanta
     */
    public function get(array $filters = []): Pagerfanta
    {
        $page = $filters['page'] ?? self::DEFAULT_PAGE;
        $limit = $filters['limit'] ?? self::DEFAULT_LIMIT;

        $queryBuilder = $this->createQueryBuilder('p')
            ->orderBy('p.id', 'DESC');

        return $this->paginate(
            query: $queryBuilder,
            page: $page,
            limit: $limit
        );
    }

    /**
     * Delete user by id
     *
     * @param User $user
     * @return void
     */
    public function delete(User $user): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->remove($user);
        $entityManager->flush();
    }

    /**
     * Upgrade password of the user
     *
     * @param PasswordAuthenticatedUserInterface $user
     * @param string $newHashedPassword
     * @return void
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    /**
     * @return User|null
     */
    public function admin(): ?User
    {
        return $this->findOneBy(['admin' => true]);
    }
}
