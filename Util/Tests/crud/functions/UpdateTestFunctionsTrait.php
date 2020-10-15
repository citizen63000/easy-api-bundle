<?php

namespace EasyApiBundle\Util\Tests\crud\functions;

use EasyApiBundle\Util\ApiProblem;
use EasyApiBundle\Util\Tests\Format;
use Symfony\Component\HttpFoundation\Response;

trait UpdateTestFunctionsTrait
{
    use crudFunctionsTestTrait;

    /**
     * PUT - Nominal case.
     * @param int|null $id
     * @param string $filename
     * @param array $params
     */
    protected function doTestUpdate(?int $id, string $filename, array $params = []): void
    {
        $id = $id ?? static::defaultEntityId;
        $params = array_merge(['id' => $id], $params);

        // Request

        $data = $this->getDataSent($filename, 'Update');
        $apiOutput = self::httpPut(['name' => static::getUpdateRouteName(), 'params' => $params], $data);
        static::assertEquals(Response::HTTP_OK, $apiOutput->getStatusCode());

        // Result

        $result = $apiOutput->getData();
        $expectedResult = $this->getExpectedResponse($filename, 'Update', $result, true);
        static::assertAssessableContent($expectedResult, $result);
        static::assertEquals($expectedResult, $result);

        // GET AFTER PUT
        $apiOutput = self::httpGet(['name' => static::getGetRouteName(), 'params' => ['id' => $expectedResult['id']]]);
        static::assertEquals(Response::HTTP_OK, $apiOutput->getStatusCode());
        $result = $apiOutput->getData();
        $expectedResult = $this->getExpectedResponse($filename, 'Update', $result, true);
        static::assertAssessableContent($expectedResult, $result);
        static::assertEquals($expectedResult, $result);
    }

    /**
     * GET - Error case - not found.
     * @param int $id
     * @param array $params
     */
    public function doTestUpdateNotFound(int $id = null, array $params = []): void
    {
        $id = $id ?? 99999999;
        $params = array_merge(['id' => $id], $params);
        $apiOutput = self::httpPut(['name' => static::getUpdateRouteName(), 'params' => $params], []);
        static::assertApiProblemError($apiOutput, Response::HTTP_NOT_FOUND, [sprintf(ApiProblem::ENTITY_NOT_FOUND, 'entity')]);
    }

    /**
     * PUT - Error case - 401 - Without authentication.
     * @param int|null $id
     * @param array $params
     */
    protected function doTestUpdateWithoutAuthentication(int $id = null, $params = []): void
    {
        $id = $id ?? static::defaultEntityId;
        $params = array_merge(['id' => $id], $params);
        $apiOutput = self::httpPut(['name' => static::getUpdateRouteName(), 'params' => $params], [], false);
        static::assertApiProblemError($apiOutput, Response::HTTP_UNAUTHORIZED, [ApiProblem::JWT_NOT_FOUND]);
    }

    /**
     * PUT - Error case - 403 - Missing right.
     * @param int|null $id
     * @param array $params
     * @param string|null $userLogin
     * @param string|null $userPassword
     */
    protected function doTestUpdateWithoutRight(int $id = null, array $params = [], string $userLogin = null, string $userPassword = null): void
    {
        $id = $id ?? static::defaultEntityId;
        $params = array_merge(['id' => $id], $params);

        if (null === $userPassword && null !== $userLogin) {
            throwException(new \Exception('$userPassword parameter cannot be null if $userLogin parameters is not null'));
        }

        if (null === $userLogin && null === $userPassword) {
            $userLogin = static::USER_NORULES_TEST_USERNAME;
            $userPassword = static::USER_NORULES_TEST_PASSWORD;
        }

        $token = self::loginHttp($userLogin, $userPassword);

        $apiOutput = self::httpPut(
            ['name' => static::getUpdateRouteName(), 'params' => $params,],
            [],
            false,
            Format::JSON,
            Format::JSON,
            ['Authorization' => self::getAuthorizationTokenPrefix() . " {$token}"]
        );

        static::assertApiProblemError($apiOutput, Response::HTTP_FORBIDDEN, [ApiProblem::RESTRICTED_ACCESS]);
    }
}
