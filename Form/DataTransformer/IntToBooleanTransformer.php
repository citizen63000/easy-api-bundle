<?php

namespace EasyApiBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

class IntToBooleanTransformer implements DataTransformerInterface
{
    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        if (null === $value) {
            return false;
        }

        return $value;
    }
}