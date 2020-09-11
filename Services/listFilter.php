<?php


namespace EasyApiBundle\Services;


use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\QueryBuilder;
use EasyApiBundle\Form\Model\FilterModel;
use EasyApiBundle\Form\Type\AbstractFilterType;
use EasyApiBundle\Form\Type\FilterType;
use EasyApiBundle\Util\AbstractRepository;
use EasyApiBundle\Util\AbstractService;
use EasyApiBundle\Util\Maker\EntityConfigLoader;
use EXSyst\Component\Swagger\Parameter;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use \Symfony\Component\Form\FormInterface;

/**
 * Service qui fabrique la requête à partir d'une classe et d'un model,
 * liste les champs autorisés à partir des groupes de serialization et du model passé,
 * en déduit un model complet (model de base + champs de l'entité des groupes),
 * fabrique le formulaire à partir du model,
 * @todo mettre en cache ces éléments,
 * @todo convenir d'une convention pour les filtres
 */
class listFilter extends AbstractService
{

//    protected const fieldConversion = [
//        'string' => 'TextFilter',
//        'integer' => 'TextFilter',
//    ];
//
//    /**+
//     * @param $type
//     * @return string
//     */
//    protected static function convertEntityTypeToFormFieldType($type)
//    {
//        if(array_key_exists($type, self::fieldConversion)) {
//            return self::fieldConversion[$type];
//        } else {
//            return 'TextFilter';
//        }
//    }
//
//    public function createFormFilter(FilterModel $model, string $entityClass, string $entityTypeClass)
//    {
//        $formFields = [];
//        $entityConfiguration = EntityConfigLoader::createEntityConfigFromEntityFullName($entityClass);
//        $modelReflection = new \ReflectionClass($model);
//        foreach ($modelReflection->getProperties() as $var) {
//            $varName = $var->getName();
//            if(!in_array($varName, self::excluded)) {
//                if($entityConfiguration->hasField($varName)){
//                    $field = $entityConfiguration->getField($varName);
//                    $formFields[$varName] = self::convertEntityTypeToFormFieldType($field->getType());
//                } else {
//                    $formFields[$varName] = 'TextFilter';
//                }
//            }
//        }
//
//        var_dump($formFields);
////        die;
//
//        $form = $this->createForm($entityTypeClass, $model, ['method' => 'GET', 'fields' => $formFields]);
//
////
////        // class annotations
////        $reader = new AnnotationReader();
////        $annotations = $reader->getClassAnnotations($r);
////        foreach ($annotations as $annotation) {
////            switch (get_class($annotation)) {
////                case 'Doctrine\ORM\Mapping\Table)':
////                    break;
////            }
////        }
//
//
//    }

    /**
     * @param FormInterface $filterForm
     * @param FilterModel $model
     * @param string $entityClass
     * @param false $count
     * @param QueryBuilder|null $qb
     * @return mixed
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

                } else {
                    if($pos = strpos($fieldName, '_')) {
                        $realFieldName = substr($fieldName, 0, $pos);
                        $operator = substr($fieldName, $pos+1);
                        $exprOperator = $operator === 'min' ? 'gt' : 'lte';
                        $qb->andWhere($qb->expr()->$exprOperator("e.{$realFieldName}", ":{$fieldName}"));
                        $qb->setParameter(":{$fieldName}", $model->$fieldName);
                    } else {
                        $qb->andWhere($qb->expr()->eq("e.{$fieldName}", ":{$fieldName}"));
                        $qb->setParameter(":{$fieldName}", $model->$fieldName);
                    }
//                    echo $model->$fieldName;die;
                }
            }
        }

//        echo $qb->getQuery()->getSQL();die;
        return AbstractRepository::paginateResult($qb, 'e.id', $model->getPage(), $model->getLimit(), $count);
    }
}