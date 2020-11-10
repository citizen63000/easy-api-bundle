<?php

namespace EasyApiBundle\Util\Forms;

use Doctrine\Common\Persistence\ManagerRegistry;
use Nelmio\ApiDocBundle\Model\Model;
use EasyApiBundle\Form\Type\AbstractApiType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormConfigBuilderInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\Form\ResolvedFormTypeInterface;
use Symfony\Component\Routing\Router;
use Symfony\Component\Validator\Constraints as Assert;

class FormSerializer
{
    use FormFieldSerializerConfigurationSetterTrait;

    /** @var FormFactory  */
    private $formFactory;

    /** @var Router  */
    private $router;

    /** @var ManagerRegistry  */
    private $doctrine;

    /** @var array  */
    private $groupsConditions = [];

    /**
     * FormDescriber constructor.
     * @param FormFactory $formFactory
     * @param Router $router
     */
    public function __construct(FormFactory $formFactory, Router $router, ManagerRegistry $doctrine)
    {
        $this->formFactory = $formFactory;
        $this->router = $router;
        $this->doctrine = $doctrine;
    }

    /**
     * @return ManagerRegistry
     */
    protected function getDoctrine()
    {
        return $this->doctrine;
    }

    /**
     * @param FormInterface $form
     * @param string|null $parentKey
     *
     * @return SerializedForm|FormInterface
     */
    public function normalize(FormInterface $form, string $parentKey = null)
    {
        return $this->setConditions($this->parseForm($form, $parentKey));
    }

    /**
     * @param SerializedForm $form
     * @param array $inheritedConditions
     *
     * @return SerializedForm
     */
    private function setConditions(SerializedForm $form, array $inheritedConditions = [])
    {
        /** @var SerializedFormField $field */
        foreach ($form->getFields() as $field) {

            $conditions = $this->getFieldConditions($field, $inheritedConditions);

            if (count($conditions) > 0) {
                $field->setConditions([$conditions]);
            }

            if ($subForm = $field->getForm()) {
                $field->setForm($this->setConditions($subForm, $conditions));
            }
        }

        return $form;
    }

    /**
     * @param Model $model
     *
     * @return bool
     */
    public function supports(Model $model): bool
    {
        return is_a($model->getType()->getClassName(), FormTypeInterface::class, true);
    }

    /**
     * @return SerializedForm
     */
    protected static function getSerializedFormInstance()
    {
        return new SerializedForm();
    }
    
    /**
     * @return SerializedFormField
     */
    protected static function getSerializedFormFieldInstance()
    {
        return new SerializedFormField();
    }

    /**
     * Parse form.
     *
     * @param FormInterface $form
     * @param string|null $parentKey
     * @param string|null $parentType
     *
     * @return SerializedForm
     */
    protected function parseForm(FormInterface $form, string $parentKey = null, string $parentType = null)
    {
        $sForm = static::getSerializedFormInstance();
        $sForm->setName($form->getName());
        $sForm->setParentType($parentType);

        foreach ($form as $name => $child) {
            $config = $child->getConfig();
            $sField = static::getSerializedFormFieldInstance();
            $sField->setName($name);
            $sField->setKey(null !== $parentKey ? "{$parentKey}.{$name}" : "{$form->getName()}.{$name}");

            if ($sField = $this->findFormType($form, $config, $sField)) {
                $sForm->addField($sField);
                if (null !== $sField->getForm()) {
                    $sField->setRequired(false);
                } else {
                    $sField->setRequired($config->getRequired());
                }
            }
        }

        return $sForm;
    }

    /**
     * Ordering fields into form and subforms.
     *
     * @param $order array with organised keys
     * @param $arrayToSort array to sort
     *
     * @return array
     */
    public static function orderingFields($order, $arrayToSort)
    {
        $result = [];

        foreach ($order as $value) {
            foreach ($arrayToSort as $elem) {
                if ($elem->getName() === $value) {
                    $result[] = $elem;
                    break;
                }
            }
        }

        //add other fields
        foreach ($arrayToSort as $elmt) {
            if (!in_array($elmt->getName(), $order, true)) {
                $result[] = $elmt;
            }
        }

        return $result;
    }

    /**
     * Find form type.
     *
     * @param FormInterface $form
     * @param FormConfigBuilderInterface $config
     * @param SerializedFormField $sField
     *
     * @return SerializedFormField
     */
    private function findFormType(FormInterface $form, FormConfigBuilderInterface $config, SerializedFormField $sField)
    {
        $type = $config->getType();

        // Group label
        if (null !== $config->getOption('attr') && isset($config->getOption('attr')['group'])) {
            $sField->setGroup("form.{$config->getOption('attr')['group']}.group");
        }

        // Validation groups
        $sField->setValidationGroups($this->getValidationGroups($form, $config, $sField));

        // if form type is not builtin in Form component => subform
        if (!$builtinFormType = self::getBuiltinFormType($type)) {

            $class = get_class($type->getInnerType());
            $subForm = $this->formFactory->create($class, $config->getData(), $config->getOptions());
            $sField->setType('form');
            $sField->setWidget('form');

            // describe subform
            $sForm = $this->parseForm($subForm, $sField->getKey(), SerializedForm::PARENT_TYPE_FORM);
            $sField->setConditionedFields(self::getChildrenFieldsConditions($form, $sField));
            $sField->setForm($sForm);
        // not a form
        } else {

            // Properties
            $sField->setLabel(self::getFieldLabel($config, $sField));
            $sField->setPlaceholder(self::getFieldPlaceholder($config, $sField));
            $sField->setConditionedFields(self::getChildrenFieldsConditions($form, $sField));

            // Group
            if (null !== $config->getOption('attr') && isset($config->getOption('attr')['group'])) {
                $sField->setGroup("form.{$config->getOption('attr')['group']}.group");
            }

            // attr
            if (null !== $config->getOption('attr')) {
                $sField->setAttr($config->getOption('attr'));
            }

            // Default value
            if (null !== $config->getOption('attr') && isset($config->getOption('attr')['defaultValue'])) {
                $sField->setDefaultValue($config->getOption('attr')['defaultValue']);
            }

            // Discriminator field value
            if (null !== $config->getOption('attr') && isset($config->getOption('attr')['discriminator'])) {
                $sField->setDiscriminator($config->getOption('attr')['discriminator']);
            }

            // Dynamic choices field value
            if (null !== $config->getOption('attr') && isset($config->getOption('attr')['dynamicChoices'])) {
                $dynamicChoices = $config->getOption('attr')['dynamicChoices'];
                $dynamicChoicesRoute = $dynamicChoices['route'];

                if (null !== $this->router->generate($dynamicChoicesRoute)) {
                    $dynamicChoices['route'] = $this->router->generate($dynamicChoicesRoute);
                    $sField->setDynamicChoices($dynamicChoices);
                }
            }

            // Format field value ("percent" or "currency")
            if (null !== $config->getOption('attr') && isset($config->getOption('attr')['format'])) {
                $format = $config->getOption('attr')['format'];
                if ('percent' === $format || 'currency' === $format) {
                    $sField->setFormat($format);
                }
            }

            // fields types
            do {

                $sField = $this->setFieldConfiguration($config, $builtinFormType->getBlockPrefix(), $sField);
                break;

            } while ($builtinFormType = $builtinFormType->getParent());
        }

        $attr = $config->getOption('attr');

        // force format
        if ($attr && array_key_exists('format', $attr) && $attr['format']) {
            $sField->setType($attr['format']);
        }

        // force widget
        if ($attr && array_key_exists('widget', $attr) && $attr['widget']) {
            $sField->setWidget($attr['widget']);
        }

        return $sField->getType() ? $sField : null;
    }

    /**
     * @param SerializedFormField $sField
     * @param FormConfigBuilderInterface $config
     * @return string
     */
    private static function getPrimaryColumnName(SerializedFormField $sField, FormConfigBuilderInterface $config)
    {
        if ($sField->isReferential()) {
            try {
                $r = new \ReflectionClass($config->getOption('class'));
                $primary = $r->hasProperty('id') ? 'id' : 'code';
            } catch (\Exception $e) {
                $primary = 'id';
            }
        } else {
            $primary = 'id';
        }

        return $primary;
    }

    /**
     * @param array $entities
     * @param SerializedFormField $sField
     * @param FormConfigBuilderInterface $config
     * @return array
     */
    private function getChoicesOfEntityField(array $entities, SerializedFormField $sField, FormConfigBuilderInterface $config)
    {
        $choices = [];
        $primary = self::getPrimaryColumnName($sField, $config);
        $attr = $config->getOption('attr');

        foreach ($entities as $key => $entity) {
            if (null !== $attr && isset($attr['discriminator'])) {
                $choices[$key] = [
                    $primary => $entity->getId(),
                    'displayName' => $entity->__toString(),
                    'discriminator' => $this->getDiscriminator($sField, $entity, $attr['discriminator']),
                ];
            } else {
                $choices[$key] = [
                    $primary => $entity->getId(),
                    'displayName' => $entity->__toString(),
                ];
            }
        }

        return $choices;
    }

    /**
     * Get field label.
     *
     * @param FormConfigBuilderInterface $config
     * @param SerializedFormField $sField
     *
     * @return mixed|string
     */
    private static function getFieldLabel(FormConfigBuilderInterface $config, SerializedFormField $sField)
    {
        $label = $config->getOption('label') ?? $sField->getKey();

        return $label ? "form.{$label}.label" : '';
    }

    /**
     * Get field placeholder.
     *
     * @param FormConfigBuilderInterface $config
     * @param SerializedFormField $sField
     *
     * @return mixed|string
     */
    private static function getFieldPlaceholder(FormConfigBuilderInterface $config, SerializedFormField $sField)
    {
        $attr = $config->getOption('attr');

        $label = $attr['placeholder'] ?? $sField->getKey();

        return "form.{$label}.placeholder";
    }

    /**
     * Save validation groups of field.
     *
     * @param FormInterface $form
     * @param FormConfigBuilderInterface $config
     * @param SerializedFormField $sField
     *
     * @return array
     */
    private function getValidationGroups(FormInterface $form, FormConfigBuilderInterface $config, SerializedFormField $sField)
    {
        $groups = [];
        $constraints = $config->getOption('constraints');

        // Add validation groups on field
        if (null !== $constraints) {
            foreach ($constraints as $constraint) {
                if ($constraint instanceof Assert\Blank) {
                    foreach ($constraint->groups as $groupName) {
                        $groups[] = str_replace(($sField->getName()), $sField->getKey(), $groupName);
                    }
                }
            }
        }

        // save conditions of form (index by fieldName path in level 0 form)
        $formType = $form->getConfig()->getType()->getInnerType();
        if ($formType instanceof AbstractApiType) {
            $constraints = $formType->getGroupsConditions();
            foreach ($constraints as $constraint => $conditions) {
                if (!in_array($constraint, $this->groupsConditions, true)) {
                    $path = str_replace(".{$sField->getName()}", '', $sField->getKey());

                    foreach ($conditions as $field => $values) {
                        $fieldExpr = explode(' ', $field);
                        $fieldName = "{$path}.{$fieldExpr[0]}";
                        $this->groupsConditions["{$path}.{$constraint}"]["{$fieldName} $fieldExpr[1]"] = $values;
                    }
                }
            }
        }

        return $groups;
    }

    /**
     * @param SerializedFormField $sField
     * @param array $inheritedConditions
     *
     * @return array
     */
    private function getFieldConditions(SerializedFormField $sField, array $inheritedConditions = [])
    {
        $conditions = $result = [];
        $possibleConstraints = ['blank', 'notblank', 'Blank', 'NotBlank'];

        foreach ($possibleConstraints as $possibleConstraint) {
            $possibleFieldConstraint = "{$sField->getKey()}.{$possibleConstraint}";

            // searching conditions on the form for this field
            if (isset($this->groupsConditions[$possibleFieldConstraint])) {
                foreach ($this->groupsConditions[$possibleFieldConstraint] as $condition => $values) {
                    $firstDifIndex = null;
                    $result = [];
                    $splitKey = explode('.', $sField->getKey());
                    $operator = explode(' ', $condition);
                    $splitCondition = explode('.', $operator[0]);

                    // count differences and construct the condition path without path
                    foreach ($splitCondition as $key => $splitC) {
                        if (!isset($splitKey[$key]) || $splitC !== $splitKey[$key]) {
                            $firstDifIndex = $firstDifIndex ?? $key;
                            $result[] = $splitC;
                        }
                    }

                    // count differencies to make relative path (./field or ../field)
                    $nbDifs = count($splitKey) - $firstDifIndex;
                    $originalPath = implode('.', $result);
                    $path = '';
                    if (!preg_match('/^\.\./', $originalPath)) {
                        if ($nbDifs > 1) {
                            $shift = SerializedForm::PARENT_TYPE_FORM === $sField->getParentForm()->getParentType() ? $nbDifs - 1 : $nbDifs;
                            $path = str_repeat('../', $shift);
                        } else {
                            $path = './';
                        }
                    }

                    // add the condition
                    $conditions[$path . $originalPath . ' ' . $operator[1]] = $values;
                }
            }
        }

        // Add inherited conditions from parent form
        foreach ($inheritedConditions as $key => $values) {

            // if form in collection, for Angular the parent field is the collection so we need two steps to find the parentForm
            $parentPath = (($parentForm = $sField->getParentForm()) && $parentForm->isInCollection()) ? '../../' : '../';

            if (preg_match('/^\.\./', $key)) {
                $path = "{$parentPath}{$key}";
            } else {
                $path = str_replace('./', $parentPath, $key);
            }

            $conditions[$path] = $values;
        }

        return $conditions;
    }

    /**
     * Get conditions children's fields.
     *
     * @param FormInterface $form
     * @param SerializedFormField $sField
     *
     * @return mixed|string
     */
    private static function getChildrenFieldsConditions(FormInterface $form, SerializedFormField $sField)
    {
        $formType = $form->getConfig()->getType()->getInnerType();
        if ($formType instanceof AbstractApiType) {
            $conditionalFields = $formType->getConditionalFields();

            return $conditionalFields[$sField->getName()] ?? [];
        }

        return [];
    }

    /**
     * Get Built in form type.
     *
     * @param ResolvedFormTypeInterface $type
     *
     * @return ResolvedFormTypeInterface|null
     */
    private static function getBuiltinFormType(ResolvedFormTypeInterface $type)
    {
        do {
            $class = get_class($type->getInnerType());

            if (FormType::class === $class) {
                return null;
            }

            if (in_array($type->getBlockPrefix(), ['entity', 'document'], true)) {
                return $type;
            }

            if (0 === strpos($class, 'Symfony\Component\Form\Extension\Core\Type\\')) {
                return $type;
            }
        } while ($type = $type->getParent());

        return null;
    }

    /**
     * Expose discriminator condition of field.
     *
     * @param SerializedFormField $sField
     * @param $entity
     * @param string $discriminatorField
     *
     * @return mixed
     */
    private function getDiscriminator(SerializedFormField $sField, $entity, string $discriminatorField)
    {
        return $entity->{'get' . ucfirst($discriminatorField)}()->{'get' . ($sField->isReferential() ? 'Code' : 'Id')}();
    }
}
