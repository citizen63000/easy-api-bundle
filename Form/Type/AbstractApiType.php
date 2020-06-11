<?php


namespace EasyApiBundle\Form\Type;

use EasyApiBundle\Util\ApiProblem;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


abstract class AbstractApiType extends AbstractType
{
    /**
     * @var string
     */
    protected static $dataClass = null;

    /**
     * exemple :
     *  [
     *      'name.blank' => ['refType.code in' => ['type_1', 'type_2'], 'refNature.code in' => ['nat_1', 'nat_2']],
     *      'name.blank' => ['refType.code notin' => ['type_3'],
     *  ]
     * meens name is blank if refType is in ['type_1', 'type_2'] AND refNature is in ['nat_1', 'nat_2']
     * OR name is blank if refType is not in ['type_3'].
     *
     * @var array
     */
    protected static $groupsConditions = [];

    /**
     * @var
     */
    protected static $validationGroups;

    /**
     * @return array
     */
    public static function getGroupsConditions()
    {
        return static::$groupsConditions;
    }

    /**
     * @param string $groupName
     *
     * @return array
     */
    public static function getGroupConditions($groupName)
    {
        return static::$groupsConditions[$groupName] ?? null;
    }

    /**
     * Return conditions by conditional fields.
     *
     * @return array
     */
    public static function getConditionalFields()
    {
        $conditionalFields = [];

        foreach (static::$groupsConditions as $constraint => $conditions) {
            $splittedConstraint = explode('.', $constraint);
            $targetFieldName = $splittedConstraint[0];

            foreach ($conditions as $fieldExpr => $condition) {
                $field = str_replace([' in', ' notin'], '', $fieldExpr);

                foreach ($conditions as $key => $cond) {
                    $fieldCond = str_replace([' in', ' notin'], '', $key);
                    if ($fieldCond === $field) {
                        $conditionalFields[$field][$targetFieldName][$constraint][] = [$key => $cond];
                    }
                }
            }
        }

        return $conditionalFields;
    }

    /**
     * @param $pEntity
     *
     * @return array
     */
    protected static function getValidationGroups($pEntity)
    {
        if (null !== self::$validationGroups) {
            return self::$validationGroups;
        }

        self::$validationGroups = ['Default'];
        foreach (static::$groupsConditions as $group => $conditions) {
            foreach ($conditions as $condition => $values) {
                $expr = explode(' ', $condition);
                $constraint = $expr[1];
                $properties = explode('.', $expr[0]);
                $value = null;

                foreach ($properties as $property) {
                    $getter = 'get'.ucfirst($property);
                    $entity = $value ?? $pEntity;

                    //boolean method
                    if (!method_exists($entity, $getter)) {
                        $getter = 'is'.ucfirst($property);
                    }

                    if (method_exists($entity, $getter)) {
                        $value = $entity->$getter();
                    }
                }

                if ('in' === $constraint && in_array($value, $values, true)) {
                    self::$validationGroups[] = $group;
                    break;
                } elseif ('notin' === $constraint && !in_array($value, $values, true)) {
                    self::$validationGroups[] = $group;
                    break;
                }
            }
        }

        return self::$validationGroups;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'csrf_protection' => false,
            'extra_fields_message' => ApiProblem::FORM_EXTRA_FIELDS_ERROR,
            'data_class' => static::$dataClass,
            'validation_groups' => function (FormInterface $form) {
                return static::getValidationGroups($form->getData());
            },
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return lcfirst(substr(static::$dataClass, strrpos(static::$dataClass, '-') + 1));
    }
}