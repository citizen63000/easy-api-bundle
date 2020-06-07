<?php


namespace EasyApiBundle\Util\Tests;

use EasyApiBundle\Tests\Format;
use Symfony\Component\HttpFoundation\Response;
use EasyApiBundle\Util\ApiProblem;

trait PUTTestTrait
{
    protected static $executeSetupOnAllTest = true;
    protected static $routeName;

    /**
     * PUT - Nominal case.
     */
    public function testPutExistingEntity()
    {
        $data = self::createPUTData();
        $expectedResult = self::createPUTResponseData();

        $apiOutput = $this->httpPut(['name' => static::$routeName, 'params' => ['id' => self::ENTITY_ID_1]], $data);

        static::assertEquals(Response::HTTP_OK, $apiOutput->getStatusCode());
        $result = $apiOutput->getData();
        static::assertArrayHasKey('createdAt', $result);
        static::assertArrayHasKey('updatedAt', $result);
        unset($result['createdAt'], $result['updatedAt']);
        static::assertEquals($expectedResult, $result);
    }

    /**
     * PUT - On unexisting entity case.
     */
    public function testPutUnexistingEntity()
    {
        $data = self::createPUTData();

        $apiOutput = $this->httpPut(['name' => static::$routeName, 'params' => ['id' => 0]], $data);

        static::assertEquals(Response::HTTP_NOT_FOUND, $apiOutput->getStatusCode());
    }

    /**
     * PUT - with only required fields case.
     */
    public function testPutOnlyRequiredFieldsEntity()
    {
        $data = self::createPutOnlyRequiredData();
        $expectedResult = self::createPutOnlyRequiredResponseData();

        $apiOutput = $this->httpPut(['name' => static::$routeName, 'params' => ['id' => self::ENTITY_ID_1]], $data);

        static::assertEquals(Response::HTTP_OK, $apiOutput->getStatusCode());
        $result = $apiOutput->getData();
        static::assertArrayHasKey('createdAt', $result);
        static::assertArrayHasKey('updatedAt', $result);
        unset($result['createdAt'], $result['updatedAt']);
        static::assertEquals($expectedResult, $result);
    }

    /**
     * PUT - Error case - 401 - Without authentication.
     */
    public function testPut401()
    {
        $data = self::createPUTResponseData();
        $apiOutput = $this->httpPut(['name' => static::$routeName, 'params' => ['id' => self::ENTITY_ID_1]], $data, false);

        static::assertApiProblemError($apiOutput, Response::HTTP_UNAUTHORIZED, [ApiProblem::JWT_NOT_FOUND]);
    }

    /**
     * PUT - Error case - 403 - Missing update right.
     */
    public function testPutWithoutRightU403()
    {
        $data = self::createPUTResponseData();
        $token = self::loginHttp('[API-TESTS-NO-RULES]', 'u-norules-pwd');
        $apiOutput = $this->httpPut([
            'name' => static::$routeName,
            'params' => ['id' => 2]],
            $data,
            false,
            Format::JSON,
            Format::JSON,
            ['Authorization' => self::getAuthorizationTokenPrefix() . " {$token}"]
        );

        static::assertApiProblemError($apiOutput, Response::HTTP_FORBIDDEN, [ApiProblem::RESTRICTED_ACCESS]);
    }
}