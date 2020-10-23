<?php

namespace EasyApiBundle\Util\Tests\crud\functions;

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
     * @param int|null $id
     * @param array $additionalParameters
     * @param string|null $userLogin
     * @param string|null $userPassword
     */
    public function doTestDelete(int $id = null, array $additionalParameters = [], string $userLogin = null, string $userPassword = null): void
    {
        $id = $id ?? static::defaultEntityId;
        $params = array_merge(['id' => $id], $additionalParameters);

        // count before delete
        $apiOutput = self::httpGetWithLogin(['name' => static::getGetListRouteName()], $userLogin, $userPassword);
        static::assertEquals(Response::HTTP_OK, $apiOutput->getStatusCode());
        $nbResults = $apiOutput->getHeaderLine('X-Total-Results');

        // delete entity
        $apiOutput = self::httpDeleteWithLogin(['name' => static::getDeleteRouteName(), 'params' => $params], $userLogin, $userPassword);
        static::assertEquals(Response::HTTP_NO_CONTENT, $apiOutput->getStatusCode());

        // try to get after delete
        $apiOutput = self::httpGetWithLogin(['name' => static::getGetRouteName(), 'params' => ['id' => $id]], $userLogin, $userPassword);
        static::assertEquals(Response::HTTP_NOT_FOUND, $apiOutput->getStatusCode());

        // count after delete
        $apiOutput = self::httpGetWithLogin(['name' => static::getGetListRouteName()], $userLogin, $userPassword);
        static::assertEquals(Response::HTTP_OK, $apiOutput->getStatusCode());
        static::assertEquals($nbResults-1, $apiOutput->getHeaderLine('X-Total-Results'));
    }

    /**
     * DELETE - Unexisting entity.
     * @param int $id
     * @param string|null $userLogin
     * @param string|null $userPassword
     */
    public function doTestDeleteNotFound(int $id, string $userLogin = null, string $userPassword = null): void
    {
        $apiOutput = self::httpDeleteWithLogin(['name' => static::getDeleteRouteName(), 'params' => ['id' => $id]], $userLogin, $userPassword);
        static::assertApiProblemError($apiOutput, Response::HTTP_NOT_FOUND, [sprintf(ApiProblem::ENTITY_NOT_FOUND, 'entity')]);
    }

    /**
     * DELETE - Error case - 401 - Without authentication.
     * @param int|null $id
     */
    public function doTestDeleteWithoutAuthentication(int $id = null): void
    {
        $apiOutput = self::httpDelete(['name' => static::getDeleteRouteName(), 'params' => ['id' => $id ?? static::defaultEntityId]], false);
        static::assertApiProblemError($apiOutput, Response::HTTP_UNAUTHORIZED, [ApiProblem::JWT_NOT_FOUND]);
    }

    /**
     * DELETE - Error case - 403 - Missing right.
     * @param int|null $id
     * @param string|null $userLogin
     * @param string|null $userPassword
     */
    public function doTestDeleteWithoutRight(int $id = null, string $userLogin = null, string $userPassword = null): void
    {
        if(null === $userLogin && null === $userPassword) {
            $userLogin = static::USER_NORULES_TEST_USERNAME;
            $userPassword = static::USER_NORULES_TEST_PASSWORD;
        }

        $apiOutput = self::httpDeleteWithLogin(['name' => static::getDeleteRouteName(), 'params' => ['id' => $id ?? static::defaultEntityId]], $userLogin, $userPassword);

        static::assertApiProblemError($apiOutput, Response::HTTP_FORBIDDEN, [ApiProblem::RESTRICTED_ACCESS]);
    }
}
