<?php

namespace EasyApiBundle\Services;

use Doctrine\ORM\QueryBuilder;
use EasyApiBundle\Exception\ApiProblemException;
use EasyApiBundle\Form\Model\FilterModel;
use EasyApiBundle\Form\Type\AbstractFilterType;
use EasyApiBundle\Util\AbstractRepository;
use EasyApiBundle\Util\AbstractService;
use EasyApiBundle\Util\ApiProblem;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use \Symfony\Component\Form\FormInterface;
use \Doctrine\ORM;
use Symfony\Component\HttpFoundation\Response;

/**
 * Service qui fabrique la requête à partir d'une classe et d'un model (dans le form),
 */
class listFilter extends AbstractService
{

    public const classAlias = 'e';

    /**
     * @param FormInterface $filterForm
     * @param string $entityClass
     * @param false $count
     * @param QueryBuilder|null $qb
     * @return mixed
     * @throws ORM\NoResultException
     * @throws ORM\NonUniqueResultException
     */
    public function filter(FormInterface $filterForm, string $entityClass, $count = false, QueryBuilder $qb = null)
    {
        /** @var FilterModel $model */
        $model = $filterForm->getData();
        $repo = $this->getRepository($entityClass);
        $classAlias = self::classAlias;
        $qb = $qb ?? $repo->createQueryBuilder($classAlias);

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
                        $qb->andWhere($qb->expr()->eq("{$classAlias}.{$fieldName}", ":{$fieldName}"));
                        $qb->setParameter(":{$fieldName}", $model->$fieldName);
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
        }

        return AbstractRepository::paginateResult($qb, "{$classAlias}.id", $model->getPage(), $model->getLimit(), $count);
    }

}
