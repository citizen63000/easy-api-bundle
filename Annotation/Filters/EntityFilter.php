<?php

namespace EasyApiBundle\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Annotation\Target("METHOD")
 */
class EntityFilter
{
    /**
     *  @var string
     * @Annotation\Required()
     */
    public $type;
}