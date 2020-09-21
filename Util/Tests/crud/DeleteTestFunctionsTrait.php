<?php

namespace EasyApiBundle\Util\Tests\crud;

use EasyApiBundle\Util\ApiProblem;
use Symfony\Component\HttpFoundation\Response;

trait DeleteTestFunctionsTrait
{
    use crudFunctionsTestTrait;

    /**
     * Set $executeSetupOnAllTest to false
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        static::$executeSetupOnAllTest = false;
    }

    /**
     * DELETE - Nominal case.
     * @param int $id
     * @param array $additionalParameters
     */
    public function doTestDelete(int $id = null, array $additionalParameters = []): void
    {
        $id = $id ?? 1;
        $params = array_merge(['id' => $id], $additionalParameters);

        // count before delete
        $apiOutput = self::httpGet(['name' => static::getGetListRouteName()]);
        static::assertEquals(Response::HTTP_OK, $apiOutput->getStatusCode());
        $nbResults = $apiOutput->getHeaderLine('X-Total-Results');

        // delete entity
        $apiOutput = self::httpDelete(['name' => static::getDeleteRouteName(), 'params' => $params]);
        static::assertEquals(Response::HTTP_NO_CONTENT, $apiOutput->getStatusCode());

        // try to get after delete
        $apiOutput = self::httpGet(['name' => static::getGetRouteName(), 'params' => ['id' => $id]]);
        static::assertEquals(Response::HTTP_NOT_FOUND, $apiOutput->getStatusCode());

        // count after delete
        $apiOutput = self::httpGet(['name' => static::getGetListRouteName()]);
        static::assertEquals(Response::HTTP_OK, $apiOutput->getStatusCode());
        static::assertEquals($nbResults-1, $apiOutput->getHeaderLine('X-Total-Results'));
    }

    /**
     * DELETE - Unexisting entity.
     * @param int $id
     */
    public function doTestDeleteNotFound(int $id): void
    {
        $apiOutput = self::httpDelete(['name' => static::getDeleteRouteName(), 'params' => ['id' => $id]]);
        self::assertEquals(Response::HTTP_NOT_FOUND, $apiOutput->getStatusCode());
    }

    /**
     * DELETE - Error case - 401 - Without authentication.
     * @param int $id
     */
    public function doTestDeleteWithoutAuthentication(int $id = null): void
    {
        $apiOutput = self::httpDelete(['name' => static::getDeleteRouteName(), 'params' => ['id' => $id ?? 1]], false);
        static::assertApiProblemError($apiOutput, Response::HTTP_UNAUTHORIZED, [ApiProblem::JWT_NOT_FOUND]);
    }

    /**
     * DELETE - Error case - 403 - Missing right.
     * @param int $id
     * @param string|null $userLogin
     * @param string|null $userPassword
     */
    public function doTestDeleteWithoutRight(int $id = null, string $userLogin = null, string $userPassword = null): void
    {
        if(null === $userPassword && null!== $userLogin) {
            throwException(new \Exception('$userPassword parameter cannot be null if $userLogin paramters is not null'));
        }

        if(null === $userLogin && null === $userPassword) {
            $userLogin = static::USER_NORULES_TEST_USERNAME;
            $userPassword = static::USER_NORULES_TEST_PASSWORD;
        }

        $token = self::loginHttp($userLogin, $userPassword);
        $apiOutput = self::httpDelete([
            'name' => static::getDeleteRouteName(), 'params' => ['id' => $id ?? 1]],
            false,
            ['Authorization' => self::getAuthorizationTokenPrefix()." {$token}"]
        );

        static::assertApiProblemError($apiOutput, Response::HTTP_FORBIDDEN, [ApiProblem::RESTRICTED_ACCESS]);
    }
}
