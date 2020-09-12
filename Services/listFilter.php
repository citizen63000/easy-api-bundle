<?php

namespace EasyApiBundle\Services;

use Doctrine\ORM\QueryBuilder;
use EasyApiBundle\Form\Model\FilterModel;
use EasyApiBundle\Form\Type\AbstractFilterType;
use EasyApiBundle\Util\AbstractRepository;
use EasyApiBundle\Util\AbstractService;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use \Symfony\Component\Form\FormInterface;
use \Doctrine\ORM;

/**
 * Service qui fabrique la requête à partir d'une classe et d'un model (dans le form),
 */
class listFilter extends AbstractService
{

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
        $qb = $qb ?? $repo->createQueryBuilder('e');

        /** @var  $field */
        foreach ($filterForm->all() as $field) {
            $fieldName = $field->getName();
            if(null !== $model->$fieldName && !in_array($fieldName, AbstractFilterType::excluded)) {
                $fieldConfig = $field->getConfig();
                $fieldType = $fieldConfig->getType()->getInnerType();

                if($fieldType instanceof EntityType) {
                    $alias = "e_{$fieldName}";
                    $qb->innerJoin("e.{$fieldName}", $alias);
                    $qb->andWhere($qb->expr()->eq("{$alias}.id", ":{$alias}"));
                    $qb->setParameter(":{$alias}", $model->$fieldName);
                } else {
                    if($pos = strpos($fieldName, '_')) { // interval like fieldName_min or fieldName_max
                        $realFieldName = substr($fieldName, 0, $pos);
                        $operator = substr($fieldName, $pos+1);
                        $exprOperator = $operator === 'min' ? 'gt' : 'lte';
                        $qb->andWhere($qb->expr()->$exprOperator("e.{$realFieldName}", ":{$fieldName}"));
                        $qb->setParameter(":{$fieldName}", $model->$fieldName);
                    } else { // value
                        $qb->andWhere($qb->expr()->eq("e.{$fieldName}", ":{$fieldName}"));
                        $qb->setParameter(":{$fieldName}", $model->$fieldName);
                    }
                }
            }
        }

        return AbstractRepository::paginateResult($qb, 'e.id', $model->getPage(), $model->getLimit(), $count);
    }
}