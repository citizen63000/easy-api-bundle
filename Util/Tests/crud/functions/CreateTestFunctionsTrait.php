<?php

namespace EasyApiBundle\Util\Tests\crud\functions;

use EasyApiBundle\Util\ApiProblem;
use EasyApiBundle\Util\Tests\Format;
use Symfony\Component\HttpFoundation\Response;

trait CreateTestFunctionsTrait
{
    use crudFunctionsTestTrait;

    /**
     * POST - Nominal case.
     * @param string $filename
     * @param array $params
     */
    protected function doTestCreate(string $filename, array $params = []): void
    {
        // Request

        $data = $this->getDataSent($filename, 'Create');
        $apiOutput = self::httpPost(['name' => static::getCreateRouteName(), 'params' => $params], $data);
        static::assertEquals(Response::HTTP_CREATED, $apiOutput->getStatusCode());

        // Result

        $result = $apiOutput->getData();
        $expectedResult = $this->getExpectedResponse($filename, 'Create', $result, true);
        static::assertAssessableContent($expectedResult, $result);
        static::assertEquals($expectedResult, $result);

        // GET AFTER POST
        $apiOutput = self::httpGet(['name' => static::getGetRouteName(), 'params' => ['id' => $expectedResult['id']]]);
        static::assertEquals(Response::HTTP_OK, $apiOutput->getStatusCode());
        $result = $apiOutput->getData();
        $expectedResult = $this->getExpectedResponse($filename, 'Create', $result, true);
        static::assertAssessableContent($expectedResult, $result);
        static::assertEquals($expectedResult, $result);
    }

    /**
     * POST - Error case - 401 - Without authentication.
     * @param array $params
     */
    protected function doTestCreateWithoutAuthentication(array $params = []): void
    {
        $apiOutput = self::httpPost(['name' => static::getCreateRouteName(), 'params' => $params], [], false);
        static::assertApiProblemError($apiOutput, Response::HTTP_UNAUTHORIZED, [ApiProblem::JWT_NOT_FOUND]);
    }

    /**
     * POST - Error case - 403 - Missing right.
     * @param array $params
     * @param string|null $userLogin
     * @param string|null $userPassword
     */
    protected function doTestCreateWithoutRight(array $params = [], string $userLogin = null, string $userPassword = null): void
    {
        if (null === $userPassword && null !== $userLogin) {
            throwException(new \Exception('$userPassword parameter cannot be null if $userLogin parameters is not null'));
        }

        if (null === $userLogin && null === $userPassword) {
            $userLogin = static::USER_NORULES_TEST_USERNAME;
            $userPassword = static::USER_NORULES_TEST_PASSWORD;
        }

        $token = self::loginHttp($userLogin, $userPassword);

        $apiOutput = self::httpPost(
            ['name' => static::getCreateRouteName(),'params' => $params,],
            [],
            false,
            Format::JSON,
            Format::JSON,
            ['Authorization' => self::getAuthorizationTokenPrefix() . " {$token}"]
        );

        static::assertApiProblemError($apiOutput, Response::HTTP_FORBIDDEN, [ApiProblem::RESTRICTED_ACCESS]);
    }
}
