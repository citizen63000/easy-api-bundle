<?php

namespace EasyApiBundle\Util\Tests\crud;

use EasyApiBundle\Util\ApiProblem;
use EasyApiBundle\Util\Tests\Format;
use Symfony\Component\HttpFoundation\Response;

trait UpdateTestFunctionsTrait
{
    use crudFunctionsTestTrait;

    /**
     * PUT - Nominal case.
     * @param string|null $filename
     * @param array $params
     */
    protected function doTestUpdate(string $filename = null, $params = []): void
    {
        // Request

        if (null !== $filename) {
            $data = $this->getDataSent($filename, 'Update');
        } else {
            $data = self::createPUTData();
        }

        $apiOutput = self::httpPut(['name' => static::getUpdateRouteName(), 'params' => $params], $data);

        static::assertEquals(Response::HTTP_CREATED, $apiOutput->getStatusCode());

        // Result

        $result = static::assertCreatedUpdatedAt($apiOutput->getData());
        if (null !== $filename) {
            $expectedResult = $this->getExpectedResponse($filename, 'Update', $apiOutput->getData());
        } else {
            $expectedResult = self::createPUTResponseData();
        }

        static::assertEquals($expectedResult, $result);

        // GET AFTER PUT
        $apiOutput = self::httpGet(['name' => static::getGetRouteName(), 'params' => ['id' => $expectedResult['id']]]);
        static::assertEquals(Response::HTTP_OK, $apiOutput->getStatusCode());
        static::assertEquals($expectedResult, $apiOutput->getData());
    }

    /**
     * PUT - Error case - 401 - Without authentication.
     * @param string|null $filename
     * @param array $params
     */
    protected function doTestUpdateWithoutAuthentication(string $filename = null, $params = []): void
    {
        if (null !== $filename) {
            $data = $this->getDataSent($filename, 'Update');
        } else {
            $data = self::getUpdateRouteName();
        }

        $apiOutput = self::httpPut(['name' => static::getUpdateRouteName(), 'params' => $params], $data, false);
        static::assertApiProblemError($apiOutput, Response::HTTP_UNAUTHORIZED, [ApiProblem::JWT_NOT_FOUND]);
    }

    /**
     * PUT - Error case - 403 - Missing right.
     * @param string|null $filename
     * @param array $params
     * @param string|null $userLogin
     * @param string|null $userPassword
     */
    protected function doTestUpdateWithoutRight(string $filename = null, array $params = [], string $userLogin = null, string $userPassword = null): void
    {
        if (null === $userPassword && null !== $userLogin) {
            throwException(new \Exception('$userPassword parameter cannot be null if $userLogin parameters is not null'));
        }

        if (null === $userLogin && null === $userPassword) {
            $userLogin = static::USER_NORULES_TEST_USERNAME;
            $userPassword = static::USER_NORULES_TEST_PASSWORD;
        }

        $token = self::loginHttp($userLogin, $userPassword);

        if (null !== $filename) {
            $data = $this->getDataSent($filename, 'Update');
        } else {
            $data = self::createPUTData();
        }

        $apiOutput = self::httpPut([
            'name' => static::getUpdateRouteName(),
            'params' => $params,
        ], $data, false, Format::JSON, Format::JSON, ['Authorization' => self::getAuthorizationTokenPrefix() . " {$token}"]);

        static::assertApiProblemError($apiOutput, Response::HTTP_FORBIDDEN, [ApiProblem::RESTRICTED_ACCESS]);
    }
}
