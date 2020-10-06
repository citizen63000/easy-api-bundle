<?php

namespace EasyApiBundle\Util\Tests\crud;


trait UpdateTestTrait
{
    use UpdateTestFunctionsTrait;

    /**
     * PUT - Nominal case.
     */
    public function testPutExistingEntity(): void
    {
        $this->doTestUpdate(1, 'update.json');
    }

    /**
     * PUT - On unexisting entity case.
     */
    public function testPutUnexistingEntity(): void
    {
        $this->doTestUpdateNotFound(999);
    }

    /**
     * PUT - Error case - 401 - Without authentication.
     */
    public function testPut401(): void
    {
        $this->doTestUpdateWithoutAuthentication(1);
    }

    /**
     * PUT - Error case - 403 - Missing update right.
     */
    public function testPutWithoutRight(): void
    {
        $this->doTestUpdateWithoutRight(1);
    }
}
