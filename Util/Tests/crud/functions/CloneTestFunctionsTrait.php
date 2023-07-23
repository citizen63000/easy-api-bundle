<?php

namespace EasyApiBundle\Util\Tests\crud\functions;

use EasyApiBundle\Util\ApiProblem;
use Symfony\Component\HttpFoundation\Response;

trait CloneTestFunctionsTrait
{
    use crudFunctionsTestTrait;

    /**
     * POST - Nominal case.
     * @param int|null $id
     * @param string $filename
     * @param array $params
     * @param bool $testGetAfterClone
     * @param string|null $userLogin

     */
    protected function doTestClone(int $id = null, string $filename = 'clone.json', array $params = [], bool $testGetAfterClone = true, string $userLogin = null): void
    {
        // Request
        $params += ['id' => $id ?? static::defaultEntityId];
        $apiOutput = self::httpPostWithLogin(['name' => static::getCloneRouteName(), 'params' => $params], $userLogin);

        // Assert result
        static::assertEquals(Response::HTTP_CREATED, $apiOutput->getStatusCode());
        $result = $apiOutput->getData();
        $expectedResult = $this->getExpectedResponse($filename, static::$cloneActionType, $result, true);
        static::assertAssessableContent($expectedResult, $result);
        static::assertEquals($expectedResult, $result, "Assert failed for file {$filename}");

        // Get after create
        if($testGetAfterClone) {
            $this->doTestGetAfterSave($expectedResult['id'], $filename, $userLogin);
        }
    }

    /**
     * POST - Error case - 401 - Without authentication.
     * @param array $params
     */
    protected function doTestCloneWithoutAuthentication(array $params = []): void
    {
        $params += ['id' => $id ?? static::defaultEntityId];
        $apiOutput = self::httpPost(['name' => static::getCloneRouteName(), 'params' => $params], [], false);
        static::assertApiProblemError($apiOutput, Response::HTTP_UNAUTHORIZED, [ApiProblem::JWT_NOT_FOUND]);
    }

    /**
     * POST - Error case - 403 - Missing right.
     * @param array $params
     * @param string|null $userLogin

     */
    protected function doTestCloneWithoutRight(array $params = [], string $userLogin = null): void
    {
        $params += ['id' => $id ?? static::defaultEntityId];

        if (null === $userLogin) {
            $userLogin = static::USER_NORULES_TEST_USERNAME;
        }

        $apiOutput = self::httpPostWithLogin(['name' => static::getCloneRouteName(), 'params' => $params], $userLogin);

        static::assertApiProblemError($apiOutput, Response::HTTP_FORBIDDEN, [ApiProblem::RESTRICTED_ACCESS]);
    }

    /**
     * POST - Error case - 403 - Forbidden action.
     * @param int|null $id
     * @param array $params
     * @param string|null $userLogin

     * @param array $messages
     * @param int $errorCode
     * @throws \Exception
     */
    protected function doTestCloneForbiddenAction(int $id = null, array $params = [], string $userLogin = null, $messages = [ApiProblem::RESTRICTED_ACCESS], $errorCode = Response::HTTP_UNPROCESSABLE_ENTITY): void
    {
        $params += ['id' => $id ?? static::defaultEntityId];

        $apiOutput = self::httpPostWithLogin(['name' => static::getCloneRouteName(), 'params' => $params], $userLogin);

        static::assertApiProblemError($apiOutput, $errorCode, $messages);
    }
}
