<?php

namespace EasyApiBundle\Services;

use Doctrine\ORM\QueryBuilder;
use EasyApiBundle\Form\Model\FilterModel;
use EasyApiBundle\Form\Type\AbstractFilterType;
use EasyApiBundle\Model\FilterResult;
use EasyApiBundle\Util\AbstractRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormConfigBuilderInterface;
use \Symfony\Component\Form\FormInterface;
use \Doctrine\ORM;

/**
 * Make the query from class and model (in form).
 */
class ListFilter extends AbstractService
{
    /** @var string class alias used in query */
    public const classAlias = 'e';

    /**
     * @param FormInterface $filterForm
     * @param string $entityClass
     * @return mixed
     * @throws ORM\NoResultException
     * @throws ORM\NonUniqueResultException
     */
    final public function filter(FormInterface $filterForm, string $entityClass)
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
                    if ($pos = strpos($fieldName, '_')) {
                        $this->linkedEntityFilter($qb, $field->getConfig(), $fieldName, $model, $joins);
                    } else {
                        // field itself
                        $this->fieldFilter($qb, $field->getConfig(), self::classAlias, $fieldName, $fieldName, $model);
                    }
                }
            }
        }

        $this->sort($qb, $model, $joins);

        if(null !== $model->getPage()) {
            $filterResult->setResults(AbstractRepository::paginateResult($qb, $model->getPage(), $model->getLimit()));
        } elseif(null !== $model->getLimit()) {
            $filterResult->setResults(AbstractRepository::paginateResult($qb, 1, $model->getLimit()));
        } else {
            $filterResult->setResults($qb->getQuery()->getResult());
        }

        $filterResult->setNbResults((int)AbstractRepository::paginateResult($qb, $model->getPage(), $model->getLimit(), true));

        return $filterResult;
    }

    /**
     * @param QueryBuilder $qb
     * @param FormConfigBuilderInterface $fieldConfig
     * @param string $fieldName
     * @param FilterModel $model
     * @param array $joins
     */
    protected function linkedEntityFilter(QueryBuilder $qb, FormConfigBuilderInterface $fieldConfig, string $fieldName, FilterModel $model, array &$joins)
    {
        $classAlias = $this->joinEntityFromPath($qb, $fieldName, $joins);
        $entityFieldName = self::getFieldNameFromPath($fieldName);
        $this->fieldFilter($qb, $fieldConfig, $classAlias, $fieldName, $entityFieldName, $model);
    }

    /**
     * @param QueryBuilder $qb
     * @param string $fieldName
     * @param array $joins
     * @return string|null
     */
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

    /**
     * @param string $path
     * @return string
     */
    protected static function getFieldNameFromPath(string $path): string
    {
        $parts = explode('_', $path);

        return $parts[count($parts)-1];
    }

    /**
     * Join entity
     * @param QueryBuilder $qb
     * @param string $classAlias
     * @param string $fieldName
     * @param array $joins
     * @return string
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

    /**
     * @param QueryBuilder $qb
     * @param FormConfigBuilderInterface $fieldConfig
     * @param string $classAlias
     * @param string $fieldName
     * @param string $entityFieldName
     * @param FilterModel $model
     */
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

    /**
     * @param QueryBuilder $qb
     * @param string $classAlias
     * @param string $fieldName
     * @param string $entityFieldName
     * @param FilterModel $model
     */
    protected function entityTypeFilter(QueryBuilder $qb, string $classAlias, string $fieldName, string $entityFieldName, FilterModel $model)
    {
        $alias = "{$classAlias}_{$entityFieldName}";
        $qb->innerJoin("{$classAlias}.{$entityFieldName}", $alias);
        $qb->andWhere($qb->expr()->eq("{$alias}", ":{$alias}"));
        $qb->setParameter(":{$alias}", $model->$fieldName);
    }

    /**
     * Interval like fieldName_min or fieldName_max.
     * @param QueryBuilder $qb
     * @param string $classAlias
     * @param string $fieldName
     * @param string $entityFieldName
     * @param FilterModel $model
     * @param int $operatorPosition
     */
    protected function intervalFilter(QueryBuilder $qb, string $classAlias, string $fieldName, string $entityFieldName, FilterModel $model, int $operatorPosition)
    {
        $realFieldName = substr($entityFieldName, 0, $operatorPosition);
        $operator = substr($entityFieldName, $operatorPosition + 1);
        $exprOperator = $operator === 'min' ? 'gt' : 'lte';
        $alias = ":{$classAlias}_{$exprOperator}_{$entityFieldName}";
        $qb->andWhere($qb->expr()->$exprOperator("{$classAlias}.{$realFieldName}", $alias));
        $qb->setParameter($alias, $model->$fieldName);
    }

    /**
     * @param QueryBuilder $qb
     * @param string $classAlias
     * @param string $fieldName
     * @param string $entityFieldName
     * @param FilterModel $model
     */
    protected function textFilter(QueryBuilder $qb, string $classAlias, string $fieldName, string $entityFieldName, FilterModel $model)
    {
        $alias = ":{$classAlias}_{$entityFieldName}";
        $qb->andWhere($qb->expr()->like("{$classAlias}.{$entityFieldName}", $alias));
        $qb->setParameter($alias, "%{$model->$fieldName}%");
    }

    /**
     * @param QueryBuilder $qb
     * @param string $classAlias
     * @param string $fieldName
     * @param string $entityFieldName
     * @param FilterModel $model
     */
    protected function defaultFilter(QueryBuilder $qb, string $classAlias, string $fieldName, string $entityFieldName, FilterModel $model)
    {
        $alias = ":{$classAlias}_{$entityFieldName}";
        $qb->andWhere($qb->expr()->eq("{$classAlias}.{$entityFieldName}", $alias));
        $qb->setParameter($alias, $model->$fieldName);
    }

    /**
     * (field1:asc|desc, field2:asc|desc, subEntity_field:asc|desc)
     * @param QueryBuilder $qb
     * @param FilterModel $model
     * @param array $joins
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

    /**
     * @param string $entityClass
     * @param FilterModel $model
     * @return QueryBuilder
     */
    protected function getFilterQueryBuilder(string $entityClass, FilterModel $model): QueryBuilder
    {
        return $this->getRepository($entityClass)->createQueryBuilder(static::classAlias)->distinct(static::classAlias);
    }
}