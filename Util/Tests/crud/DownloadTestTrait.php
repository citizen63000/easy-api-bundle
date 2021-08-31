<?php

namespace EasyApiBundle\Util\Tests\crud;

use EasyApiBundle\Util\Tests\crud\functions\DownloadTestFunctionsTrait;
use EasyApiBundle\Util\Tests\crud\functions\GetTestFunctionsTrait;

trait DownloadTestTrait
{
    use DownloadTestFunctionsTrait;
    use GetTestFunctionsTrait;

    protected static function initExecuteSetupOnAllTest()
    {
        static::$executeSetupOnAllTest = false;
    }

    /**
     * @return string
     */
    protected static function getGetRouteName()
    {
        return self::getDownloadRouteName();
    }

    /**
     * GET - Nominal case.
     */
    public function testDownload(): void
    {
        $this->doTestDownload();
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