<?php

namespace App\Repository;

use App\Entity\Media;
use App\Traits\HasPagination;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Pagerfanta\Pagerfanta;

/**
 * @method Media|null find($id, $lockMode = null, $lockVersion = null)
 * @method Media|null findOneBy(array $criteria, array $orderBy = null)
 * @method Media[]    findAll()
 * @method Media[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MediaRepository extends ServiceEntityRepository
{
    use HasPagination;

    /**
     * MediaRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Media::class);
    }

    /**
     * Get paginated media
     *
     * @param array $filters
     *
     * @return Pagerfanta
     */
    public function get(array $filters = []): Pagerfanta
    {
        $page = $filters['page'] ?? self::DEFAULT_PAGE;
        $limit = $filters['limit'] ?? self::DEFAULT_LIMIT;
        $user = $filters['user'] ?? null;
        $album = $filters['album'] ?? null;
        $activeUser = $filters['active_user'] ?? null;

        $queryBuilder = $this->createQueryBuilder('p')
            ->orderBy('p.id', 'DESC');

        if ($user !== null) {
            $queryBuilder->andWhere('p.user = :user')
                ->setParameter('user', $user);
        }

        if ($album !== null) {
            $queryBuilder->andWhere('p.album = :album')
                ->setParameter('album', $album);
        }

        if ($activeUser === true) {
            $queryBuilder
                ->innerJoin('p.user', 'u')
                ->andWhere('u.active = :active')
                ->setParameter('active', true);
        }

        return $this->paginate(
            query: $queryBuilder,
            page: $page,
            limit: $limit
        );
    }
}
