<?php

namespace EasyApiBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;

class FilterType extends AbstractFilterType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
    }
}