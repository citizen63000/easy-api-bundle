<?php

namespace EasyApiBundle\Util\Tests\crud;

/**
 * testPutExistingEntity
 * testPutUnexistingEntity
 * testPutWithoutAuthentication
 * testPutWithoutRight
 */
trait UpdateTestTrait
{
    use UpdateTestFunctionsTrait;

    /**
     * PUT - Nominal case.
     */
    public function testPutExistingEntity(): void
    {
        $this->doTestUpdate(1, 'nominalCase.json');
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
    public function testPutWithoutAuthentication(): void
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
