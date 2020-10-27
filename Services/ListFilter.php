<?php

namespace EasyApiBundle\Services;

use Doctrine\ORM\QueryBuilder;
use EasyApiBundle\Form\Model\FilterModel;
use EasyApiBundle\Form\Type\AbstractFilterType;
use EasyApiBundle\Model\FilterResult;
use EasyApiBundle\Util\AbstractRepository;
use EasyApiBundle\Util\AbstractService;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use \Symfony\Component\Form\FormInterface;
use \Doctrine\ORM;

/**
 * Service qui fabrique la requête à partir d'une classe et d'un model (dans le form),
 */
class ListFilter extends AbstractService
{
    public const classAlias = 'e';

    /**
     * @param FormInterface $filterForm
     * @param string $entityClass
     * @return mixed
     * @throws ORM\NoResultException
     * @throws ORM\NonUniqueResultException
     */
    public final function filter(FormInterface $filterForm, string $entityClass)
    {
        /** @var FilterModel $model */
        $model = $filterForm->getData();
        $classAlias = self::classAlias;
        $qb = $this->getFilterQueryBuilder($entityClass, $model);
        $filterResult = new FilterResult();

        // value filters
        foreach ($filterForm->all() as $field) {
            $fieldName = $field->getName();
            if(null !== $model->$fieldName && !in_array($fieldName, AbstractFilterType::excluded)) {
                $fieldConfig = $field->getConfig();
                $fieldType = $fieldConfig->getType()->getInnerType();
                if($fieldType instanceof EntityType) {
                    $alias = "{$classAlias}_{$fieldName}";
                    $qb->innerJoin("{$classAlias}.{$fieldName}", $alias);
                    $qb->andWhere($qb->expr()->eq("{$alias}.id", ":{$alias}"));
                    $qb->setParameter(":{$alias}", $model->$fieldName);
                } else {
                    if($pos = strpos($fieldName, '_')) { // interval like fieldName_min or fieldName_max
                        $realFieldName = substr($fieldName, 0, $pos);
                        $operator = substr($fieldName, $pos+1);
                        $exprOperator = $operator === 'min' ? 'gt' : 'lte';
                        $qb->andWhere($qb->expr()->$exprOperator("{$classAlias}.{$realFieldName}", ":{$fieldName}"));
                        $qb->setParameter(":{$fieldName}", $model->$fieldName);
                    } else { // value
                        if($fieldType instanceof TextType) {
                            $qb->andWhere($qb->expr()->like("{$classAlias}.{$fieldName}", ":{$fieldName}"));
                            $qb->setParameter(":{$fieldName}", "%{$model->$fieldName}%");
                        } else  {
                            $qb->andWhere($qb->expr()->eq("{$classAlias}.{$fieldName}", ":{$fieldName}"));
                            $qb->setParameter(":{$fieldName}", $model->$fieldName);
                        }
                    }
                }
            }
        }

        // sort (field1:asc|desc, field2:asc|desc)
        if(!empty($model->getSort())) {
            $strOrders = explode(',', $model->getSort());
            foreach ($strOrders as $order) {
                $parts = explode(':', $order);
                $qb->addOrderBy("{$classAlias}.{$parts[0]}", $parts[1]);
            }
        } elseif(null !== $model->getDefaultSort()) {
            foreach ($model->getDefaultSort() as $field => $direction) {
                $qb->addOrderBy("{$classAlias}.{$field}", $direction);
            }
        }

        $filterResult->setResults(AbstractRepository::paginateResult($qb, "{$classAlias}.id", $model->getPage(), $model->getLimit()));
        $filterResult->setNbResults((int) AbstractRepository::paginateResult($qb, "{$classAlias}.id", $model->getPage(), $model->getLimit(), true));

        return $filterResult;
    }

    /**
     * @param string $entityClass
     * @param FilterModel $model
     * @return QueryBuilder
     */
    protected function getFilterQueryBuilder(string $entityClass, FilterModel $model): QueryBuilder
    {
        return $this->getRepository($entityClass)->createQueryBuilder(listFilter::classAlias);
    }

}
