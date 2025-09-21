<?php

namespace EasyApiBundle\Services;

use Doctrine\ORM;
use Doctrine\ORM\QueryBuilder;
use EasyApiBundle\Form\Model\FilterModel;
use EasyApiBundle\Form\Type\AbstractFilterType;
use EasyApiBundle\Model\FilterResult;
use EasyApiBundle\Util\AbstractRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormConfigBuilderInterface;
use Symfony\Component\Form\FormInterface;

/**
 * Make the query from class and model (in form).
 */
class ListFilter extends AbstractService
{
    /** @var string class alias used in query */
    public const string classAlias = 'e';
    public const bool useDistinct = true;

    /**
     * @throws ORM\NoResultException
     * @throws ORM\NonUniqueResultException
     */
    final public function filter(FormInterface $filterForm, string $entityClass): FilterResult
    {
        /** @var FilterModel $model */
        $model = $filterForm->getData();
        $qb = $this->getFilterQueryBuilder($entityClass, $model);
        $filterResult = new FilterResult();
        $joins = [];

        // value filters
        foreach ($filterForm->all() as $fieldName => $field) {
            if (null !== $model->$fieldName && !in_array($fieldName, AbstractFilterType::excluded)) {
                if (method_exists($this, $method = "apply{$fieldName}")) {
                    $this->$method($qb, $model->$fieldName);
                } else {
                    // linked entity var
                    if ($pos = strpos($fieldName, '_') && !($pos = strpos($fieldName, '__'))) {
                        $this->linkedEntityFilter($qb, $field->getConfig(), $fieldName, $model, $joins);
                    } else {
                        // field itself
                        $this->fieldFilter($qb, $field->getConfig(), self::classAlias, $fieldName, $fieldName, $model);
                    }
                }
            }
        }

        $this->sort($qb, $model, $joins);

        if (null !== $model->getPage()) {
            $filterResult->setResults($this->paginateResult($qb, $model->getPage(), $model->getLimit()));
        } elseif (null !== $model->getLimit()) {
            $filterResult->setResults($this->paginateResult($qb, 1, $model->getLimit()));
        } else {
            $filterResult->setResults($qb->getQuery()->getResult());
        }

        $filterResult->setNbResults($this->countResults($qb, $model->getPage(), $model->getLimit(), true));

        return $filterResult;
    }

    /**
     * @throws ORM\NoResultException
     * @throws ORM\NonUniqueResultException
     */
    protected function paginateResult(QueryBuilder $qb, int $page = null, int $limit = null)
    {
        return AbstractRepository::paginateResult($qb, $page, $limit);
    }

    /**
     * @throws ORM\NoResultException
     * @throws ORM\NonUniqueResultException
     */
    protected function countResults(QueryBuilder $qb, int $page = null, int $limit = null): int
    {
        return (int) AbstractRepository::paginateResult($qb, $page, $limit, true);
    }

    protected function linkedEntityFilter(QueryBuilder $qb, FormConfigBuilderInterface $fieldConfig, string $fieldName, FilterModel $model, array &$joins)
    {
        $classAlias = $this->joinEntityFromPath($qb, $fieldName, $joins);
        $entityFieldName = self::getFieldNameFromPath($fieldName);
        $this->fieldFilter($qb, $fieldConfig, $classAlias, $fieldName, $entityFieldName, $model);
    }

    protected function joinEntityFromPath(QueryBuilder $qb, string $fieldName, array &$joins): ?string
    {
        $parts = explode('_', $fieldName);
        $nbParts = count($parts) - 1;

        $classAlias = self::classAlias;
        for ($i = 0; $i < $nbParts; ++$i) {
            $classAlias = $this->joinEntity($qb, $classAlias, $parts[$i], $joins);
        }

        return $classAlias;
    }

    protected static function getFieldNameFromPath(string $path): string
    {
        $parts = explode('_', $path);

        return $parts[count($parts) - 1];
    }

    /**
     * Join entity.
     */
    protected function joinEntity(QueryBuilder $qb, string $classAlias, string $fieldName, array &$joins): string
    {
        $join = "{$classAlias}.{$fieldName}";
        $alias = "{$classAlias}_{$fieldName}";

        if (!isset($joins[$join])) {
            $qb->innerJoin($join, $alias);
            $joins[$join] = $alias;
        }

        return $alias;
    }

    protected function fieldFilter(QueryBuilder $qb, FormConfigBuilderInterface $fieldConfig, string $classAlias, string $fieldName, string $entityFieldName, FilterModel $model)
    {
        $fieldType = $fieldConfig->getType()->getInnerType();
        if ($fieldType instanceof EntityType) {
            $this->entityTypeFilter($qb, $classAlias, $fieldName, $entityFieldName, $model);
        } else {
            if ($pos = strpos($entityFieldName, '__')) { // interval like fieldName_min or fieldName_max
                $this->intervalFilter($qb, $classAlias, $fieldName, $entityFieldName, $model, $pos);
            } else { // value
                if ($fieldType instanceof TextType) {
                    $this->textFilter($qb, $classAlias, $fieldName, $entityFieldName, $model);
                } else {
                    $this->defaultFilter($qb, $classAlias, $fieldName, $entityFieldName, $model);
                }
            }
        }
    }

    protected function entityTypeFilter(QueryBuilder $qb, string $classAlias, string $fieldName, string $entityFieldName, FilterModel $model)
    {
        $alias = "{$classAlias}_{$entityFieldName}";
        $qb->innerJoin("{$classAlias}.{$entityFieldName}", $alias);
        $qb->andWhere($qb->expr()->eq("{$alias}", ":{$alias}"));
        $qb->setParameter(":{$alias}", $model->$fieldName);
    }

    /**
     * Interval like fieldName_min or fieldName_max.
     */
    protected function intervalFilter(QueryBuilder $qb, string $classAlias, string $fieldName, string $entityFieldName, FilterModel $model, int $operatorPosition)
    {
        $realFieldName = substr($entityFieldName, 0, $operatorPosition);
        $operator = substr($entityFieldName, $operatorPosition + 1);
        $exprOperator = '_min' === $operator ? 'gte' : 'lte';
        $alias = ":{$classAlias}_{$exprOperator}_{$entityFieldName}";
        $qb->andWhere($qb->expr()->$exprOperator("{$classAlias}.{$realFieldName}", $alias));
        $qb->setParameter($alias, $model->$fieldName);
    }

    protected function textFilter(QueryBuilder $qb, string $classAlias, string $fieldName, string $entityFieldName, FilterModel $model)
    {
        $alias = ":{$classAlias}_{$entityFieldName}";
        $qb->andWhere($qb->expr()->like("{$classAlias}.{$entityFieldName}", $alias));
        $qb->setParameter($alias, "%{$model->$fieldName}%");
    }

    protected function defaultFilter(QueryBuilder $qb, string $classAlias, string $fieldName, string $entityFieldName, FilterModel $model)
    {
        $alias = ":{$classAlias}_{$entityFieldName}";
        $qb->andWhere($qb->expr()->eq("{$classAlias}.{$entityFieldName}", $alias));
        $qb->setParameter($alias, $model->$fieldName);
    }

    /**
     * (field1:asc|desc, field2:asc|desc, subEntity_field:asc|desc).
     */
    protected function sort(QueryBuilder $qb, FilterModel $model, array &$joins)
    {
        $classAlias = self::classAlias;
        if (!empty($model->getSort())) {
            $strOrders = explode(',', $model->getSort());
            foreach ($strOrders as $order) {
                $parts = explode(':', $order);

                $fieldClassAlias = $this->joinEntityFromPath($qb, $parts[0], $joins);
                $entityFieldName = self::getFieldNameFromPath($parts[0]);

                $qb->addOrderBy("{$fieldClassAlias}.{$entityFieldName}", $parts[1]);
            }
        } elseif (null !== $model->getDefaultSort()) {
            foreach ($model->getDefaultSort() as $field => $direction) {
                $qb->addOrderBy("{$classAlias}.{$field}", $direction);
            }
        }
    }

    protected function getFilterQueryBuilder(string $entityClass, FilterModel $model): QueryBuilder
    {
        $qb = $this->getRepository($entityClass)->createQueryBuilder(static::classAlias);

        return static::useDistinct ? $qb->distinct(static::classAlias) : $qb;
    }
}
