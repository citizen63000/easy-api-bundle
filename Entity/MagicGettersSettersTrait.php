<?php

namespace EasyApiBundle\Entity;

trait MagicGettersSettersTrait
{
    public function __get($property)
    {
        return $this->property;
    }

    public function __set($property, $value)
    {
        $this->property = $value;
    }
}
