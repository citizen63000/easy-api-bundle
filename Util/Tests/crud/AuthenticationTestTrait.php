<?php

namespace EasyApiBundle\Util\Tests\crud;

use EasyApiBundle\Util\Tests\crud\functions\AuthenticationTestFunctionsTrait;
use Symfony\Component\HttpFoundation\Response;

trait AuthenticationTestTrait
{
    use AuthenticationTestFunctionsTrait;

    protected static $authenticateRouteName = 'fos_user_security_check';
    protected static $refreshTokenRouteName = 'gesdinet_jwt_refresh_token';

    protected static function initExecuteSetupOnAllTest()
    {
        static::$executeSetupOnAllTest = false;
    }

    public function testAuthenticateWithGoodCredentials()
    {
        $params = ['username' => static::USER_TEST_USERNAME, 'password' => static::USER_TEST_PASSWORD];

        // Request
        $apiOutput = self::httpPost(['name' => static::$authenticateRouteName], $params, false);

        // Assert token
        static::assertEquals(Response::HTTP_OK, $apiOutput->getStatusCode());
        self::checkAuthenticateResponse($apiOutput->getData());
    }

    public function testAuthenticateWithBadCredentials()
    {
        $params = [
            'username' => 'thisusernotexist',
            'password' => 'thisusernotexist',
        ];

        // Request
        $apiOutput = self::httpPost(['name' => static::$authenticateRouteName], $params, false);
        static::assertEquals(Response::HTTP_UNAUTHORIZED, $apiOutput->getStatusCode());
        static::assertEquals(['errors' => ['core.error.bad_credentials']], $apiOutput->getData());
    }

    public function testRefreshToken()
    {
        $params = ['username' => static::USER_TEST_USERNAME, 'password' => static::USER_TEST_PASSWORD];

        // get refreshToken
        $apiOutput = self::httpPost(['name' => static::$authenticateRouteName], $params, false);
        static::assertEquals(Response::HTTP_OK, $apiOutput->getStatusCode());
        $result = $apiOutput->getData();
        self::arrayHasKey('refreshToken', $result);

        // test refreshToken route
        $apiOutput = self::httpPost(['name' => static::$refreshTokenRouteName], ['refreshToken' => $result['refreshToken']], false);
        static::assertEquals(Response::HTTP_OK, $apiOutput->getStatusCode());
        self::checkAuthenticateResponse($apiOutput->getData());
    }

    public function testRefreshTokenWithBadRefreshToken()
    {
        // Request
        $apiOutput = self::httpPost(['name' => static::$refreshTokenRouteName], ['refreshToken' => 'imfakerefreshtoken'], false);
        static::assertEquals(Response::HTTP_NOT_FOUND, $apiOutput->getStatusCode());
        static::assertEquals(['errors' => ['core.error.token.not_found']], $apiOutput->getData());
    }
}