<?php

namespace EasyApiBundle\Form\Type;

use EasyApiBundle\Form\Type\MediaUploader\AbstractMediaType;
use EasyApiBundle\Util\ApiProblem;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractApiType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'managePreSubmitAbstractMediaFiles']);
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'manageAbstractMediaFiles']);
    }

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
     * @var array
     */
    protected $validationGroups;

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
    protected function getValidationGroups($pEntity)
    {
        $this->validationGroups = ['Default'];
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
                    $this->validationGroups[] = $group;
                    break;
                } elseif ('notin' === $constraint && !in_array($value, $values, true)) {
                    $this->validationGroups[] = $group;
                    break;
                }
            }
        }

        return $this->validationGroups;
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
                return $this->getValidationGroups($form->getData());
            },
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return static::getDataClassShortName();
    }

    /**
     * @return string
     */
    protected static function getDataClassShortName()
    {
        return lcfirst(substr(static::$dataClass, strrpos(static::$dataClass, '\\') + 1));
    }

    /**
     * Set empty AbstractMedia entity to null in media container entity to delete it
     * Ex : myFile: { filename: null, file: null} => myFile: null
     * @param FormEvent $event
     */
    public function managePreSubmitAbstractMediaFiles(FormEvent $event)
    {

        $data = $event->getData();
        $form = $event->getForm();

        if (!$data) {
            return;
        }

        foreach ($form as $name => $child) {
            $config = $child->getConfig();
            $type = $config->getType();
            if($type->getInnerType() instanceof AbstractMediaType) {
                if(array_key_exists($name, $data)) {
                    if(is_array($data[$name])) {
                        $filename = $data[$name]['filename'] ?? null;
                        $file = $data[$name]['file'] ?? null;
                        if(null === $filename && null === $file) {
                            $data[$name] = null;
                            $this->valuesToSetNull[] = $name;
                        }
                    } elseif (null === $data[$name]) {
                        $this->valuesToSetNull[] = $name;
                    }
                }
            }
        }

        $event->setData($data);
    }

    /**
     * Set null data fields to null in entity
     * Ex : myFile: { filename: null, file: null} => myFile: null
     * @param FormEvent $event
     */
    public function manageAbstractMediaFiles(FormEvent $event)
    {
        $data = $event->getData();

        foreach ($this->valuesToSetNull as $fieldName) {
            $data->{'set'.ucfirst($fieldName)}(null);
            $event->setData($data);
        }
    }
}