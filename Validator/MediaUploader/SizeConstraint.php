<?php

namespace EasyApiBundle\Validator\MediaUploader;

use Symfony\Component\Validator\Constraint;

class SizeConstraint extends Constraint
{
    public const INVALID_MAX_SIZE = 'invalid.max_size';

    /**
     * @return array|string
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}