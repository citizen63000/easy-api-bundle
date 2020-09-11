<?php


namespace EasyApiBundle\Form\Type;


use Symfony\Component\Form\FormBuilderInterface;

class FilterType extends AbstractFilterType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        foreach ($options['fields'] as $fieldName => $filterType) {
            $method = "add{$filterType}";
            static::$method($builder, $fieldName);
        }
    }
}