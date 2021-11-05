<?php

namespace EasyApiBundle\Validator\MediaUploader;

use EasyApiBundle\Entity\MediaUploader\AbstractMedia;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class MimeConstraintValidator extends ConstraintValidator
{
    /**
     * @param AbstractMedia $entity
     * @param Constraint $constraint
     */
    public function validate($entity, Constraint $constraint)
    {
        if ($file = $entity->getFile()) {
            if (!in_array($file->getMimeType(), $entity->getMimeTypes())) {
                $this->buildViolation(MimeConstraint::forbidden);
            }
        }
    }

    /**
     * @param $violation
     */
    protected function buildViolation($violation): void
    {
        $this->context->buildViolation($violation)->atPath('sort')->addViolation();
    }
}
