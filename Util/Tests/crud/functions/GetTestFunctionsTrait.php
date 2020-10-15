<?php

namespace EasyApiBundle\Util\Tests\crud\functions;

use EasyApiBundle\Util\ApiProblem;
use EasyApiBundle\Util\Tests\Format;
use ReflectionException;
use Symfony\Component\HttpFoundation\Response;

trait GetTestFunctionsTrait
{
    use crudFunctionsTestTrait;

    /**
     * GET - Nominal case.
     * @param int|null $id
     * @param string|null $filename
     */
    public function doTestGet(int $id = null, string $filename = null): void
    {
        $id = $id ?? static::defaultEntityId;

        $apiOutput = self::httpGet(['name' => static::getGetRouteName(), 'params' => ['id' => $id]]);

        self::assertEquals(Response::HTTP_OK, $apiOutput->getStatusCode());
        $result = $apiOutput->getData();

        if(null !== $filename) {
            $expectedResult = $this->getExpectedResponse($filename, 'Get', $result);
        } else {
            $expectedResult = self::createGETResponseData();
        }

        static::assertAssessableContent($expectedResult, $result);
        static::assertEquals($expectedResult, $result);
    }

    /**
     * GET - Error case - not found.
     * @param int $id
     */
    public function doTestGetNotFound(int $id = null): void
    {
        $apiOutput = self::httpGet(['name' => static::getGetRouteName(), 'params' => ['id' => $id ?? 99999999]]);
        static::assertApiProblemError($apiOutput, Response::HTTP_NOT_FOUND, [sprintf(ApiProblem::ENTITY_NOT_FOUND, 'entity')]);
    }

    /**
     * GET - Error case - 401 - Without authentication.
     * @param int $id
     */
    public function doTestGetWithoutAuthentication(int $id = null): void
    {
        $apiOutput = self::httpGet(['name' => static::getGetRouteName(), 'params' => ['id' => $id ?? static::defaultEntityId]], false);
        static::assertApiProblemError($apiOutput, Response::HTTP_UNAUTHORIZED, [ApiProblem::JWT_NOT_FOUND]);
    }

    /**
     * GET - Error case - 403 - Missing right.
     * @param int|null $id
     * @param string|null $userLogin
     * @param string|null $userPassword
     */
    public function doTestGetWithoutRight(int $id = null, string $userLogin = null, string $userPassword = null): void
    {
        if(null === $userPassword && null!== $userLogin) {
            throwException(new \Exception('$userPassword parameter cannot be null if $userLogin parameters is not null'));
        }

        if(null === $userLogin && null === $userPassword) {
            $userLogin = static::USER_NORULES_TEST_USERNAME;
            $userPassword = static::USER_NORULES_TEST_PASSWORD;
        }

        $token = self::loginHttp($userLogin, $userPassword);
        $apiOutput = self::httpGet([
            'name' => static::getGetRouteName(), 'params' => ['id' => $id ?? static::defaultEntityId]],
            false,
            Format::JSON,
            ['Authorization' => self::getAuthorizationTokenPrefix()." {$token}"]
        );

        static::assertApiProblemError($apiOutput, Response::HTTP_FORBIDDEN, [ApiProblem::RESTRICTED_ACCESS]);
    }
}
