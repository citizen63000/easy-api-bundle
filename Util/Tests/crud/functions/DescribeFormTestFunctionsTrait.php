<?php

namespace EasyApiBundle\Util\Tests\crud\functions;

use EasyApiBundle\Util\ApiProblem;
use Symfony\Component\HttpFoundation\Response;

trait DescribeFormTestFunctionsTrait
{
    use crudFunctionsTestTrait;

    /**
     * Set $executeSetupOnAllTest to false
     */
    public static function setUpBeforeClass(): void
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
        $params['method'] = $method;
        $apiOutput = self::httpGetWithLogin(['name' => static::getDescribeFormRouteName(), 'params' => $params], $userLogin, $userPassword);

        $result = $apiOutput->getData();

        $expectedResult = $this->getExpectedResponse(strtolower($method).'.json', 'DescribeForm', $result);

        self::assertEquals(Response::HTTP_OK, $apiOutput->getStatusCode());
        static::assertAssessableContent($expectedResult, $result);
        self::assertEquals($expectedResult, $result);
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
     * @param string|null $userLogin
     * @param string|null $userPassword
     * @param array $params
     */
    public function doTestGetDescribeFormWithoutRight(string $userLogin = null, string $userPassword = null, array $params = []): void
    {
        if(null === $userLogin && null === $userPassword) {
            $userLogin = static::USER_NORULES_TEST_USERNAME;
            $userPassword = static::USER_NORULES_TEST_PASSWORD;
        }

        $apiOutput = self::httpGetWithLogin(['name' => static::getDescribeFormRouteName(), 'params' => $params], $userLogin, $userPassword);

        static::assertApiProblemError($apiOutput, Response::HTTP_FORBIDDEN, [ApiProblem::RESTRICTED_ACCESS]);
    }
}
