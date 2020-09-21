<?php

namespace EasyApiBundle\Util\Tests\crud;

use EasyApiBundle\Util\ApiProblem;
use EasyApiBundle\Util\Tests\Format;
use Symfony\Component\HttpFoundation\Response;

trait DescribeFormTestFunctionsTrait
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
     * @param string $method
     * @param array $params
     * @param string|null $userLogin
     * @param string|null $userPassword
     */
    protected function doGetTest(string $method, array $params = [], string $userLogin = null, string $userPassword = null)
    {
        //$expectedResult = self::createDescribeFormPostResponseData();

        if(null === $userLogin) {
            $apiOutput = self::httpGet(['name' => static::getDescribeFormRouteName(), 'params' => ['method' => $method]]);
        } elseif(null !== $userLogin && null !== $userPassword) {
            $token = self::loginHttp($userLogin, $userPassword);
            $apiOutput = self::httpGet(
                ['name' => static::getDescribeFormRouteName(), 'params' => $params],
                false,
                Format::JSON,
                ['Authorization' => self::getAuthorizationTokenPrefix()." {$token}"]
            );
        } else {
            throwException(new \Exception('$userPassword parameter cannot be null if $userLogin parameters is not null'));
        }

        self::assertEquals(Response::HTTP_OK, $apiOutput->getStatusCode());
        //self::assertEquals($expectedResult, $apiOutput->getData());
    }

    /**
     * Nominal case for post form.
     * @param array $params
     * @param string|null $userLogin
     * @param string|null $userPassword
     */
    public function doTestGetDescribeFormForPost(array $params = [], string $userLogin = null, string $userPassword = null): void
    {
        $this->doGetTest('POST', $params, $userLogin, $userPassword);
    }

    /**
     * Nominal case for put form.
     * @param array $params
     * @param string|null $userLogin
     * @param string|null $userPassword
     */
    public function doTestGetDescribeFormForPut(array $params = [], string $userLogin = null, string $userPassword = null): void
    {
        $this->doGetTest('PUT', $params, $userLogin, $userPassword);
    }

    /**
     * GET - Error case - 401 - Without authentication.
     * @param array $params
     */
    public function doTestGetDescribeFormWithoutAuthentication(array $params = []): void
    {
        $apiOutput = self::httpGet(['name' => static::getDescribeFormRouteName(), 'params' => $params], false);
        self::assertEquals(Response::HTTP_UNAUTHORIZED, $apiOutput->getStatusCode());
    }

    /**
     * GET - Error case - 403 - Missing ADMIN role.
     * @param string $userLogin
     * @param string $userPassword
     * @param array $params
     */
    public function doTestGetDescribeFormWithoutRight(string $userLogin = null, string $userPassword = null, array $params = []): void
    {
        if(null === $userPassword && null!== $userLogin) {
            throwException(new \Exception('$userPassword parameter cannot be null if $userLogin paramters is not null'));
        }

        if(null === $userLogin && null === $userPassword) {
            $userLogin = static::USER_NORULES_TEST_USERNAME;
            $userPassword = static::USER_NORULES_TEST_PASSWORD;
        }

        $token = self::loginHttp($userLogin, $userPassword);
        $apiOutput = self::httpGet([
            'name' => static::getDescribeFormRouteName(), 'params' => $params], false, Format::JSON, ['Authorization' => self::getAuthorizationTokenPrefix()." {$token}"]);

        static::assertApiProblemError($apiOutput, Response::HTTP_FORBIDDEN, [ApiProblem::RESTRICTED_ACCESS]);
    }
}
