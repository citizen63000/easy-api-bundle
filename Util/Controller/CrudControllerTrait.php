<?php

namespace EasyApiBundle\Util\Controller;

/**
 * Trait CrudControllerTrait
 * @package EasyApiBundle\Util\Controller
 */
trait CrudControllerTrait
{
    use crudGetControllerTrait;
    use crudCreateControllerTrait;
    use crudUpdateControllerTrait;
    use crudDeleteControllerTrait;
    use CrudDescribeFormControllerTrait;
}