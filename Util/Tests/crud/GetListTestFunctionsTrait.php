<?php

namespace EasyApiBundle\Util\Tests\crud;

use EasyApiBundle\Util\ApiProblem;
use EasyApiBundle\Util\Tests\Format;
use Symfony\Component\HttpFoundation\Response;

trait GetListTestFunctionsTrait
{
    use crudFunctionsTestTrait;

    /**
     * GET - Nominal case.
     * @param string|null $filename
     * @throws \ReflectionException
     */
    public function doTestGetList(string $filename = null): void
    {
        $apiOutput = self::httpGet(['name' => static::getGetListRouteName(), 'params' => []]);

        self::assertEquals(Response::HTTP_OK, $apiOutput->getStatusCode());
        $result = $apiOutput->getData();

        if(null !== $filename) {
            $dir = $this->getCurrentDir().'/Responses/GetList';
            $filePath = "{$dir}/{$filename}";
            if(!file_exists($filePath)) {
                if(!is_dir($dir)) {
                    mkdir($dir, 0777, true);
                }
                file_put_contents($filePath, json_encode($result));
            }
            $expectedResult = json_decode(file_get_contents($filePath), true);
        } else {
            $expectedResult = self::createGETListResponseData();
        }

        static::assertEquals($expectedResult, $result);
    }

    /**
     * GET - Error case - not found - Without authentication.
     */
    public function doTestGetListNotFound(): void
    {
        $apiOutput = self::httpGet(['name' => static::getGetListRouteName(), 'params' => []]);
        static::assertApiProblemError($apiOutput, Response::HTTP_NOT_FOUND, [ApiProblem::ENTITY_NOT_FOUND]);
    }

    /**
     * GET - Error case - 401 - Without authentication.
     */
    public function doTestGetWithoutAuthentication(): void
    {
        $apiOutput = self::httpGet(['name' => static::getGetListRouteName(), 'params' => []], false);
        static::assertApiProblemError($apiOutput, Response::HTTP_UNAUTHORIZED, [ApiProblem::JWT_NOT_FOUND]);
    }

    /**
     * GET - Error case - 403 - Missing right.
     * @param string|null $userLogin
     * @param string|null $userPassword
     */
    public function doTestGetWithoutRight(string $userLogin = null, string $userPassword = null): void
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
            'name' => static::getGetRouteName(), 'params' => []],
            false,
            Format::JSON,
            ['Authorization' => self::getAuthorizationTokenPrefix()." {$token}"]
        );

        static::assertApiProblemError($apiOutput, Response::HTTP_FORBIDDEN, [ApiProblem::RESTRICTED_ACCESS]);
    }
}
