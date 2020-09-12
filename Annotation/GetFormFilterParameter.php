<?php

namespace EasyApiBundle\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Annotation\Target("METHOD")
 */
class GetFormFilterParameter extends GetFormParameter
{
    /**
     *  @var string
     * @Annotation\Required()
     */
    public $entityClass;

    /**
     *  @var array
     * @Annotation\Required()
     */
    public $fields;

    /**
     *  @var array
     * @Annotation\Required()
     */
    public $sortFields;
}