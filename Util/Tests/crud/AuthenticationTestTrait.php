<?php

namespace EasyApiBundle\Util\Tests\crud;

use EasyApiBundle\Util\Tests\crud\functions\GetTestFunctionsTrait;
use Namshi\JOSE\JWS;
use Symfony\Component\HttpFoundation\Response;

trait AuthenticationTestTrait
{
    use GetTestFunctionsTrait;

    protected static $routeName = 'fos_user_security_check';

    protected static function initExecuteSetupOnAllTest()
    {
        static::$executeSetupOnAllTest = false;
    }

    /**
     * @param array $payload
     */
    protected function checkPayloadContent(array $payload)
    {
        self::assertArrayHasKey('iat', $payload);
        self::assertArrayHasKey('exp', $payload);
        self::assertEquals($payload['iat'] + self::$container->getParameter('jwt_token_ttl'), $payload['exp']);
    }

    public function testAuthenticateWithGoodCredentials()
    {
        $filename = 'authenticateWithGoodCredentials.json';
        $params = [
            'username' => static::USER_TEST_USERNAME,
            'password' => static::USER_TEST_PASSWORD,
        ];

        // Request
        $apiOutput = self::httpPost(['name' => static::$routeName], $params, false);

        // Assert result
        static::assertEquals(Response::HTTP_OK, $apiOutput->getStatusCode());
        $result = $apiOutput->getData();
        self::arrayHasKey('token', $result);
        self::arrayHasKey('refreshToken', $result);
        self::checkPayloadContent($token = JWS::load($result['token'])->getPayload());
    }

    public function testAuthenticateWithBadCredentials()
    {
        $filename = 'authenticateWithGoodCredentials.json';
        $params = [
            'username' => 'thisusernotexist',
            'password' => 'thisusernotexist',
        ];

        // Request
        $apiOutput = self::httpPost(['name' => static::$routeName], $params, false);
        static::assertEquals(Response::HTTP_UNAUTHORIZED, $apiOutput->getStatusCode());
        static::assertEquals(['errors' => ['core.error.bad_credentials']], $apiOutput->getData());
    }
}