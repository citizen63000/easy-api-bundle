<?php

namespace EasyApiBundle\Util\Tests\crud\functions;

use EasyApiBundle\Util\ApiProblem;
use Symfony\Component\HttpFoundation\Response;

trait GetTestFunctionsTrait
{
    use crudFunctionsTestTrait;

    /**
     * GET - Nominal case.
     * @param int|null $id
     * @param string|null $filename
     * @param string|null $userLogin
     */
    public function doTestGet(int $id = null, string $filename = 'nominalCase.json', string $userLogin = null): void
    {
        self::doTestGenericGet(['id' => $id ?? static::defaultEntityId], $filename, $userLogin);
    }

    /**
     * @param array $params
     * @param string|null $filename
     * @param string|null $userLogin
     */
    public function doTestGenericGet(array $params = [], string $filename = 'nominalCase.json', string $userLogin = null)
    {
        $apiOutput = self::httpGetWithLogin(static::generateGetRouteParameters($params), $userLogin);

        self::assertEquals(Response::HTTP_OK, $apiOutput->getStatusCode());
        $result = $apiOutput->getData();
        $expectedResult = $this->getExpectedResponse($filename, static::$getActionType, $result);

        static::assertAssessableContent($expectedResult, $result);
        static::assertEquals($expectedResult, $result, "Assert failed for file {$filename}");
    }

    /**
     * GET - Error case - not found.
     * @param int|null $id
     * @param string|null $userLogin
     */
    public function doTestGetNotFound(int $id = null, string $userLogin = null): void
    {
        self::doTestGenericGetNotFound(['id' => $id ?? 99999999], $userLogin);
    }

    /**
     * @param array $params
     * @param string|null $userLogin
     */
    public function doTestGenericGetNotFound(array $params = [], string $userLogin = null): void
    {
        $apiOutput = self::httpGetWithLogin(static::generateGetRouteParameters($params), $userLogin);
        static::assertApiProblemError($apiOutput, Response::HTTP_NOT_FOUND, [sprintf(ApiProblem::ENTITY_NOT_FOUND, 'entity')]);
    }

    /**
     * GET - Error case - Without authentication.
     * @param int|null $id
     */
    public function doTestGetWithoutAuthentication(int $id = null): void
    {
        self::doTestGenericGetWithoutAuthentication(['id' => $id ?? static::defaultEntityId]);
    }

    /**
     * GET - Error case - Without authentication.
     * @param array $params
     */
    public function doTestGenericGetWithoutAuthentication(array $params = []): void
    {
        $apiOutput = self::httpGet(static::generateGetRouteParameters($params), false);
        static::assertApiProblemError($apiOutput, Response::HTTP_UNAUTHORIZED, [ApiProblem::JWT_NOT_FOUND]);
    }

    /**
     * GET - Error case - Missing right.
     * @param int|null $id
     * @param string|null $userLogin
     */
    public function doTestGetWithoutRight(int $id = null, string $userLogin = null): void
    {
        self::doTestGenericGetWithoutRight(['id' => $id ?? static::defaultEntityId], $userLogin);
    }

    /**
     * GET - Error case - Missing right.
     * @param array $params
     * @param string|null $userLogin
     */
    public function doTestGenericGetWithoutRight(array $params = [], string $userLogin = null): void
    {
        if (null === $userLogin) {
            $userLogin = static::USER_NORULES_TEST_USERNAME;
        }

        $apiOutput = self::httpGetWithLogin(static::generateGetRouteParameters($params), $userLogin);

        static::assertApiProblemError($apiOutput, Response::HTTP_FORBIDDEN, [ApiProblem::RESTRICTED_ACCESS]);
    }
}
