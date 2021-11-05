<?php

namespace EasyApiBundle\Validator\MediaUploader;

use Symfony\Component\Validator\Constraint;

class MimeConstraint extends Constraint
{
    public const forbidden = 'mime.forbidden';

    /**
     * @return string
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}