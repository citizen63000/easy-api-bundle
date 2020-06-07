<?php


namespace EasyApiBundle\Util\Tests;

use EasyApiBundle\Tests\Format;
use Symfony\Component\HttpFoundation\Response;
use EasyApiBundle\Util\ApiProblem;

trait GETLISTTestTrait
{
    protected static $executeSetupOnAllTest = false;
    protected static $routeName;
    
    /**
     * GET - Nominal case.
     */
    public function testGet()
    {
        $expectedResult = self::createGETLISTResponseData();

        $apiOutput = $this->httpGet(['name' => static::$routeName]);

        self::assertEquals(Response::HTTP_OK, $apiOutput->getStatusCode());
        $results = $apiOutput->getData();
        foreach ($results as $key => $result) {
            static::assertArrayHasKey('createdAt', $result);
            static::assertArrayHasKey('updatedAt', $result);
            unset($results[$key]['createdAt'], $results[$key]['updatedAt']);
        }
        self::assertEquals($expectedResult, $results);
    }

    /**
     * GET - Error case - 401 - Without authentication.
     */
    public function testGet401()
    {
        $apiOutput = $this->httpGet([
            'name' => static::$routeName],
            false
        );

        self::assertEquals(Response::HTTP_UNAUTHORIZED, $apiOutput->getStatusCode());
    }

    /**
     * GET - Error case - 403 - Missing list right.
     */
    public function testGetWithoutRightL403()
    {
        $token = self::loginHttp('[API-TESTS-NO-RULES]', 'u-norules-pwd');
        $apiOutput = $this->httpGet(['name' => static::$routeName,
            'params' => ['customerId' => 1]],
            false,
            Format::JSON,
            ['Authorization' => self::getAuthorizationTokenPrefix()." {$token}"]
        );

        static::assertApiProblemError($apiOutput, Response::HTTP_FORBIDDEN, [ApiProblem::RESTRICTED_ACCESS]);
    }
}