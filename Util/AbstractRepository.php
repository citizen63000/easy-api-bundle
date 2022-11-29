<?php

namespace EasyApiBundle\Util;

use Doctrine\Persistence\ObjectRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use EasyApiBundle\Exception\ApiProblemException;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use EasyApiBundle\Form\Model\FilterModel;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class AbstractRepository
 * Abstract repository, provides some common functions and behaviors.
 */
abstract class AbstractRepository extends EntityRepository
{
    /**
     * Regex for sort param.
     *
     * @var string
     */
    public const PARAM_SORT_REGEX = '#^(([a-zA-Z0-9\-\_]+\:(asc|desc))|([a-zA-Z0-9\-\_]+\:(asc|desc)\,)+([a-zA-Z0-9\-\_]+\:(asc|desc)))$#';

    /**
     * Default pagination limit.
     *
     * @var int
     */
    public const DEFAULT_PAGINATION_LIMIT = 15;

    // region Fields

    /**
     * Result field : id.
     *
     * @var string
     */
    public const FIELD_ID = 'id';

    /**
     * Result field : username.
     *
     * @var string
     */
    public const FIELD_USERNAME = 'username';

    // endregion

    /**
     * @param string $repository
     *
     * @return ObjectRepository
     */
    protected function getRepository(string $repository)
    {
        return $this->getEntityManager()->getRepository($repository);
    }

    /**
     * Validates pagination params.
     *
     * @param int|null $page Page, should be null OR > 0
     * @param int|null $resultsPerPage Number of results per page, should be null OR > 0, default is 10 if page given
     *
     * @return array with start and end offsets
     *
     */
    protected static function validatePagination(int $page = null, int $resultsPerPage = null)
    {
        if ((null !== $page) && !preg_match('#^[0-9]*[1-9]\d*$#', $page)) {
            throw new ApiProblemException(
                new ApiProblem(Response::HTTP_BAD_REQUEST, ApiProblem::PAGINATION_INCORRECT_PAGE_VALUE)
            );
        }
        if ((null !== $resultsPerPage) && !preg_match('#^[0-9]*[1-9]\d*$#', $resultsPerPage)) {
            throw new ApiProblemException(
                new ApiProblem(Response::HTTP_BAD_REQUEST, ApiProblem::PAGINATION_INCORRECT_RESULT_PER_PAGE_VALUE)
            );
        }

        $n = $resultsPerPage ?? self::DEFAULT_PAGINATION_LIMIT;
        $start = (null === $page) ? 0 : ($page - 1) * $n;

        return [$start, $n];
    }

    /**
     * Validates the sort param.
     *
     * @param array $fields
     * @param string|null $sort
     *
     * @return array|string
     *
     * @throws ApiProblemException
     */
    public static function validateSort(array $fields, string $sort = null)
    {
        if (null === $sort) {
            return $sort;
        }

        if (preg_match(self::PARAM_SORT_REGEX, strtolower($sort))) {
            $strOrders = explode(',', $sort);
            $orders = [];
            foreach ($strOrders as $order) {
                $parts = explode(':', $order);
                if (in_array($parts[0], $fields, true)) {
                    $orders[$parts[0]] = $parts[1];
                } else {
                    throw new ApiProblemException(
                        new ApiProblem(Response::HTTP_BAD_REQUEST, ApiProblem::RESULT_ORDER_INCORRECT)
                    );
                }
            }

            return $orders;
        }

        throw new ApiProblemException(
            new ApiProblem(Response::HTTP_BAD_REQUEST, ApiProblem::RESULT_SORT_MALFORMED)
        );
    }

    /**
     * @param FilterModel $search
     * @param false $count
     * @param QueryBuilder $qb
     * @return mixed
     * @throws NonUniqueResultException|NoResultException
     */
    public function filter(FilterModel $search, $count = false, QueryBuilder $qb)
    {
        $qb = $qb ?? $this->createQueryBuilder('q');

        return static::paginateResult($qb, $search->getPage(), $search->getLimit(), $count);
    }

    /**
     * @param QueryBuilder $qb
     * @param int|null $page
     * @param int|null $limit
     * @param bool $count
     *
     * @return mixed
     *
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public static function paginateResult(QueryBuilder $qb, int $page = null, int $limit = null, bool $count = false)
    {
        if ($count) {
            $qb->select($qb->expr()->count(1));
            $qb->setFirstResult(0);
            $qb->setMaxResults(1);

            return $qb->getQuery()->getSingleScalarResult();
        }

        $limit = $limit ?? 10;
        $firstResult = null !== $page ? ($page - 1) * $limit : 0;

        $qb->setMaxResults($limit);
        $qb->setFirstResult($firstResult);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param $entity
     *
     * @return mixed
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    protected function persistAndFlush(&$entity)
    {
        $this->_em->persist($entity);
        $this->_em->flush();

        return $entity;
    }
}
