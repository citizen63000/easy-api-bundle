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
        $mimeTypes = $entity->getMimeTypes();
        if ($file = $entity->getFile() && !empty($mimeTypes)) {
            if (!in_array($file->getMimeType(), $mimeTypes)) {
                $this->context->buildViolation(MimeConstraint::forbidden)->atPath('file')->addViolation();
            }
        }
    }
}
