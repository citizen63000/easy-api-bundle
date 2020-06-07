<?php


namespace EasyApiBundle\Util\Forms;


use Symfony\Component\Form\FormConfigBuilderInterface;

trait FormFieldSerializerConfigurationSetterTrait
{
    /**
     * @return string[]
     */
    protected static function getAuthorizedBlockPrefixes()
    {
        return ['text', 'textarea', 'number', 'integer', 'hidden', 'date', 'datetime', 'choice', 'checkbox', 'password', 'collection', 'entity'];
    }

    /**
     * @param FormConfigBuilderInterface $config
     * @param string $blockPrefix
     * @param SerializedFormField $field
     * @return SerializedFormField
     */
    protected function setFieldConfiguration(FormConfigBuilderInterface $config, string $blockPrefix, SerializedFormField $field)
    {
        if(in_array($blockPrefix, static::getAuthorizedBlockPrefixes())) {

            $method = 'set'.strtoupper($blockPrefix).'FieldConfiguration';

            return $this->$method($config, $field);
        }

        $field->setType($blockPrefix);

        return $field;
    }

    /**
     * @param SerializedFormField $field
     * @return SerializedFormField
     */
    protected function setTextFieldConfiguration(FormConfigBuilderInterface $config, SerializedFormField $field)
    {
        $field->setType('string');
        return $field;
    }

    /**
     * @param FormConfigBuilderInterface $config
     * @param SerializedFormField $field
     * @return SerializedFormField
     */
    protected function setTextareaFieldConfiguration(FormConfigBuilderInterface $config, SerializedFormField $field)
    {
        $field->setType('text');
        return $field;
    }

    /**
     * @param FormConfigBuilderInterface $config
     * @param SerializedFormField $field
     * @return SerializedFormField
     */
    protected function setNumberFieldConfiguration(FormConfigBuilderInterface $config, SerializedFormField $field)
    {
        $field->setType('number');
        return $field;
    }

    /**
     * @param FormConfigBuilderInterface $config
     * @param SerializedFormField $field
     * @return SerializedFormField
     */
    protected function setIntegerFieldConfiguration(FormConfigBuilderInterface $config, SerializedFormField $field)
    {
        $field->setType('integer');
        return $field;
    }

    /**
     * @param FormConfigBuilderInterface $config
     * @param SerializedFormField $field
     * @return SerializedFormField
     */
    protected function setHiddenFieldConfiguration(FormConfigBuilderInterface $config, SerializedFormField $field)
    {
        $field->setType('string');
        $field->setWidget('hidden');
        return $field;
    }

    /**
     * @param FormConfigBuilderInterface $config
     * @param SerializedFormField $field
     * @return SerializedFormField
     */
    protected function setDateFieldConfiguration(FormConfigBuilderInterface $config, SerializedFormField $field)
    {
        $field->setType('string');
        $field->setFormat('date');
        $field->setWidget('datepicker');
        return $field;
    }

    /**
     * @param FormConfigBuilderInterface $config
     * @param SerializedFormField $field
     * @return SerializedFormField
     */
    protected function setDatetimeFieldConfiguration(FormConfigBuilderInterface $config, SerializedFormField $field)
    {
        $field->setType('string');
        $field->setFormat('date-time');
        $field->setWidget('datepicker');
        return $field;
    }

    /**
     * @param FormConfigBuilderInterface $config
     * @param SerializedFormField $field
     * @return SerializedFormField
     */
    protected function setChoiceFieldConfiguration(FormConfigBuilderInterface $config, SerializedFormField $field)
    {
        if ($config->getOption('multiple')) {
            $field->setType('array');
            $field->setWidget('choice-multiple');
        } else {
            $field->setType('string');
            $field->setWidget('choice');
        }

        if ($choices = $config->getOption('choices')) {
            $values = [];
            foreach ($choices as $value => $key) {
                $values[$key] = $value;
            }

            $field->setValues($values);
        }

        return $field;
    }

    /**
     * @param FormConfigBuilderInterface $config
     * @param SerializedFormField $field
     * @return SerializedFormField
     */
    protected function setCheckboxFieldConfiguration(FormConfigBuilderInterface $config, SerializedFormField $field)
    {
        $field->setType('boolean');
        $field->setWidget('checkbox');

        if (null !== $config->getOption('attr') && isset($config->getOption('attr')['format'])) {
            $format = $config->getOption('attr')['format'];
            if ('true_false' === $format) {
                $field->setWidget($format);
            }
        }

        return $field;
    }

    /**
     * @param FormConfigBuilderInterface $config
     * @param SerializedFormField $field
     * @return SerializedFormField
     */
    protected function setPasswordFieldConfiguration(FormConfigBuilderInterface $config, SerializedFormField $field)
    {
        $field->setType('string');
        $field->setFormat('password');

        return $field;
    }

    /**
     * @param FormConfigBuilderInterface $config
     * @param SerializedFormField $field
     * @return SerializedFormField
     */
    protected function setCollectionFieldConfiguration(FormConfigBuilderInterface $config, SerializedFormField $field)
    {
        $subType = $config->getOption('entry_type');
        $subOptions = $config->getOption('entry_options');
        $subForm = $this->formFactory->create($subType, null, $subOptions);

        $field->setType('array');
        $field->setWidget('form-multiple');

        // describe subform
        $form = $field->parseForm($subForm, $field->getKey(), SerializedForm::PARENT_TYPE_COLLECTION);
        $field->setForm($form);

        return $field;
    }

    /**
     * @param FormConfigBuilderInterface $config
     * @param SerializedFormField $field
     * @return SerializedFormField
     */
    protected function setEntityFieldConfiguration(FormConfigBuilderInterface $config, SerializedFormField $field)
    {
        $field->setType('entity');

        if ($config->getOption('multiple')) {
            $field->setFormat('array');
        }

        $attr = $config->getOption('attr');

        if ($qb = $config->getOption('query_builder')) {
            $entities = $qb->getQuery()->execute();
            $choices = [];
            foreach ($entities as $key => $entity) {

                if (null !== $attr && isset($attr['discriminator'])) {
                    $choices[$key] = [
                        $field->isReferential() ? 'code' : 'id' => $entity->getId(),
                        'displayName' => $entity->__toString(),
                        'discriminator' => $this->getDiscriminator($field, $entity, $attr['discriminator']),
                    ];
                } else {
                    $choices[$key] = [
                        $field->isReferential() ? 'code' : 'id' => $entity->getId(),
                        'displayName' => $entity->__toString(),
                    ];
                }
            }

            $field->setValues($choices);

            if (null !== $attr && isset($attr['widget'])) {
                $field->setWidget($attr['widget']);
            } else {
                $field->setWidget('choice' . ($config->getOption('multiple') ? '-multiple' : ''));
            }

        } elseif (null !== $attr && array_key_exists('dynamicChoices', $attr)) {

            $field->setValues([]);
            $field->setWidget('choice' . ($config->getOption('multiple') ? '-multiple' : ''));

        } elseif (null !== $attr && array_key_exists('display', $attr) && $attr['display']) {

            $field->setWidget('choice');

        } else {

            $field->setWidget('hidden');

        }

        if ($config->getOption('multiple')) {
            $field->setFormat('array');
            $field->setWidget('choice-multiple');
        }

        return $field;
    }
}