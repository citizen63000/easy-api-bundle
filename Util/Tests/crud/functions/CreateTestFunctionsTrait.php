<?php

namespace EasyApiBundle\Util\Tests\crud\functions;

use EasyApiBundle\Util\ApiProblem;
use Symfony\Component\HttpFoundation\Response;

trait CreateTestFunctionsTrait
{
    use crudFunctionsTestTrait;

    /**
     * POST - Nominal case.
     * @param string $filename
     * @param array $params
     * @param bool $testGetAfterCreate
     * @param string|null $userLogin
     * @param string|null $userPassword
     * @throws \Exception
     */
    protected function doTestCreate(string $filename, array $params = [], bool $testGetAfterCreate = true, string $userLogin = null, string $userPassword = null): void
    {
        $data = $this->getDataSent($filename, self::$createActionType);

        // Request
        $apiOutput = self::httpPostWithLogin(['name' => static::getCreateRouteName(), 'params' => $params], $userLogin, $userPassword, $data);

        // Assert result
        static::assertEquals(Response::HTTP_CREATED, $apiOutput->getStatusCode());
        $result = $apiOutput->getData();
        $expectedResult = $this->getExpectedResponse($filename, static::$createActionType, $result, true);
        static::assertAssessableContent($expectedResult, $result);
        static::assertEquals($expectedResult, $result, "Assert failed for file {$filename}");

        // Get after create
        if($testGetAfterCreate) {
            $this->doTestGetAfterSave($expectedResult['id'], $filename, $userLogin, $userPassword);
        }
    }

    /**
     * Test Invalid submitted data case, fox example invalid data in a field with constraint
     * @param string $filename
     * @param array $params
     * @param array $expectedErrors
     * @param int|string $expectedStatusCode
     * @param string|null $userLogin
     * @param string|null $userPassword
     * @throws \Exception
     */
    protected function doTestCreateInvalid(string $filename, array $params = [], array $expectedErrors, string $expectedStatusCode = Response::HTTP_UNPROCESSABLE_ENTITY, string $userLogin = null, string $userPassword = null): void
    {
        $data = $this->getDataSent($filename, self::$createActionType);
        $apiOutput = self::httpPostWithLogin(['name' => static::getCreateRouteName(), 'params' => $params], $userLogin, $userPassword, $data);
        self::assertEquals($expectedStatusCode, $apiOutput->getStatusCode());
        self::assertEquals(['errors' => $expectedErrors], $apiOutput->getData());
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
        if (null === $userLogin && null === $userPassword) {
            $userLogin = static::USER_NORULES_TEST_USERNAME;
            $userPassword = static::USER_NORULES_TEST_PASSWORD;
        }

        $apiOutput = self::httpPostWithLogin(['name' => static::getCreateRouteName(), 'params' => $params], $userLogin, $userPassword, []);

        static::assertApiProblemError($apiOutput, Response::HTTP_FORBIDDEN, [ApiProblem::RESTRICTED_ACCESS]);
    }

    /**
     * POST - Error case - 403 - Forbidden action.
     * @param string|null $filename
     * @param array $params
     * @param string|null $userLogin
     * @param string|null $userPassword
     * @param array $messages
     * @param int $errorCode
     * @throws \Exception
     */
    protected function doTestCreateForbiddenAction(string $filename = null, array $params = [], string $userLogin = null, string $userPassword = null, array $messages = [ApiProblem::RESTRICTED_ACCESS], $errorCode = Response::HTTP_UNPROCESSABLE_ENTITY): void
    {
        $data = null != $filename ? $this->getDataSent($filename, self::$createActionType) : [];

        $apiOutput = self::httpPostWithLogin(['name' => static::getCreateRouteName(), 'params' => $params], $userLogin, $userPassword, $data);

        static::assertApiProblemError($apiOutput, $errorCode, $messages);
    }
}
