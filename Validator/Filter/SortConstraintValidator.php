<?php

namespace EasyApiBundle\Validator\Filter;

use EasyApiBundle\Form\Model\FilterModel;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class SortConstraintValidator extends ConstraintValidator
{
    protected const PARAM_SORT_REGEX = '#^(([a-zA-Z0-9\-\_]+\:(asc|desc))|([a-zA-Z0-9\-\_]+\:(asc|desc)\,)+([a-zA-Z0-9\-\_]+\:(asc|desc)))$#';

    /**
     * @param FilterModel $entity
     * @param Constraint $constraint
     */
    public function validate($entity, Constraint $constraint)
    {
        if(!empty($entity->getSort())) {
            if (preg_match(self::PARAM_SORT_REGEX, strtolower($entity->getSort()))) {
                $strOrders = explode(',', $entity->getSort());
                foreach ($strOrders as $order) {
                    $parts = explode(':', $order);
                    if (!in_array($parts[0], $entity->getSortFields(), true)) {
//                        echo sprintf(SortConstraint::invalidFieldSort, $parts[0]);die;
                        $this->buildViolation(sprintf(SortConstraint::invalidFieldSort, $parts[0]));
                    }
                }
            } else {
                $this->buildViolation(SortConstraint::malformedSort);
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