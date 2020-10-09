<?php

namespace EasyApiBundle\Util\Tests\crud;

use EasyApiBundle\Util\Tests\crud\functions\DeleteTestFunctionsTrait;

trait DeleteTestTrait
{
    use DeleteTestFunctionsTrait;

    protected static function initExecuteSetupOnAllTest()
    {
        static::$executeSetupOnAllTest = false;
    }


    /**
     * DELETE - Nominal case.
     */
    public function testDelete(): void
    {
        $this->doTestDelete();
    }

    /**
     * DELETE - Unexisting entity.
     */
    public function testDeleteNotFound(): void
    {
        $this->doTestDeleteNotFound(9999999);
    }

    /**
     * DELETE - Error case - 401 - Without authentication.
     */
    public function testDeleteWithoutAuthentication(): void
    {
        $this->doTestDeleteWithoutAuthentication();
    }

    /**
     * DELETE - Error case - 403 - Missing right.
     */
    public function testDeleteWithoutRight403(): void
    {
        $this->doTestDeleteWithoutRight();
    }
}
