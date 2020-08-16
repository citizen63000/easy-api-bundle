<?php

namespace EasyApiBundle\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Annotation\Target("METHOD")
 */
class GetFormParameter
{
    /**
     *  @var string
     * @Annotation\Required()
     */
    public $type;
}