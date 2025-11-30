<?php

namespace App\Traits;

use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Doctrine\ORM\QueryAdapter;

/**
 * @method createQueryBuilder(string $string)
 */
trait HasPagination
{
    private const int DEFAULT_PAGE = 1;
    private const int DEFAULT_LIMIT = 20;

    /**
     * Paginate results
     *
     * @param QueryBuilder $query
     * @param int $page
     * @param int $limit
     *
     * @return Pagerfanta
     */
    public function paginate(QueryBuilder $query, int $page = self::DEFAULT_PAGE, int $limit = self::DEFAULT_LIMIT): Pagerfanta
    {
        $pager = new Pagerfanta(new QueryAdapter($query));

        $pager->setMaxPerPage($limit);
        $pager->setCurrentPage($page);

        return $pager;
    }
}
