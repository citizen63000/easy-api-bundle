<?php

namespace Tests;

use Fidroit\ApiBundle\Util\Tests\Format;

trait crudFunctionsTestTrait
{
    /**
     * @return string
     */
    protected static function getGetRouteName()
    {
        return static::baseRouteName.'_get';
    }

    /**
     * @return string
     */
    protected static function getGetListRouteName()
    {
        return static::baseRouteName.'_list';
    }

    /**
     * @return string
     */
    protected static function getCreateRouteName()
    {
        return static::baseRouteName.'_create';
    }

    /**
     * @return string
     */
    protected static function getUpdateRouteName()
    {
        return static::baseRouteName.'_update';
    }

    /**
     * @return string
     */
    protected static function getDeleteRouteName()
    {
        return static::baseRouteName.'_delete';
    }

    /**
     * @return string
     */
    protected static function getDescribeFormRouteName()
    {
        return static::baseRouteName.'_describe_form';
    }
}
