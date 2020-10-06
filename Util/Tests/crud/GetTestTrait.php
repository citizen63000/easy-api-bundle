<?php

namespace EasyApiBundle\Util\Tests\crud;

trait GetTestTrait
{
    use GetTestFunctionsTrait;

    /**
     * @var bool
     */
    protected static $executeSetupOnAllTest = false;

    /**
     * GET - Nominal case.
     */
    public function testGet(): void
    {
        $this->doTestGet(1, 'nominalCase.json');
    }

    /**
     * GET - Unexisting entity.
     */
    public function testGetNotFound(): void
    {
        $this->doTestGetNotFound();
    }

    /**
     * GET - Error case - 401 - Without authentication.
     */
    public function testGetWithoutAuthentication(): void
    {
        $this->doTestGetWithoutAuthentication();
    }

    /**
     * GET - Error case - 403 - Missing read right.
     */
    public function testGetWithoutRight(): void
    {
        $this->doTestGetWithoutRight();
    }
}
