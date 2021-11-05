<?php

namespace EasyApiBundle\Validator\MediaUploader;

use EasyApiBundle\Entity\MediaUploader\AbstractMedia;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class SizeConstraintValidator extends ConstraintValidator
{
    /**
     * @param AbstractMedia $entity
     * @param Constraint $constraint
     */
    public function validate($entity, Constraint $constraint)
    {
        if ($file = $entity->getFile()) {
            if ($file->getSize() > $entity->getMaxSize()) {
                $this->context->buildViolation(SizeConstraint::INVALID_MAX_SIZE)->atPath('file')->addViolation();
            }
        }
    }
}
