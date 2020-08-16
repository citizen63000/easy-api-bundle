<?php

namespace EasyApiBundle\Util\Controller;

trait CrudControllerTrait
{
    use CrudGetControllerTrait;
    use CrudCreateControllerTrait;
    use CrudUpdateControllerTrait;
    use CrudDeleteControllerTrait;
    use CrudDescribeFormControllerTrait;
}