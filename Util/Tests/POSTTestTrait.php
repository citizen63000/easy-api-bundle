<?php


namespace EasyApiBundle\Util\Tests;

use EasyApiBundle\Tests\Format;
use Symfony\Component\HttpFoundation\Response;
use EasyApiBundle\Util\ApiProblem;

trait POSTTestTrait
{
    protected static $executeSetupOnAllTest = true;
    protected static $routeName;

    /**
     * POST - Nominal case.
     */
    public function testPostSimple()
    {
        $data = self::createPOSTData();
        $expectedResult = self::createPOSTResponseData();

        $apiOutput = $this->httpPost(['name' => static::$routeName], $data);
        static::assertEquals(Response::HTTP_CREATED, $apiOutput->getStatusCode());
        $result = $apiOutput->getData();
        static::assertArrayHasKey('createdAt', $result);
        static::assertArrayHasKey('updatedAt', $result);
        unset($result['createdAt'], $result['updatedAt']);
        static::assertEquals($expectedResult, $result);

        // GET AFTER POST
        $apiOutput = $this->httpGet(['name' => '{{ route_name_get }}', 'params' => ['id' => $expectedResult['id']]]);
        static::assertEquals(Response::HTTP_OK, $apiOutput->getStatusCode());
        $result = $apiOutput->getData();
        static::assertArrayHasKey('createdAt', $result);
        static::assertArrayHasKey('updatedAt', $result);
        unset($result['createdAt'], $result['updatedAt']);
        static::assertEquals($expectedResult, $result);
    }

    /**
     * POST - with only required fields case.
     */
    public function testPostOnlyRequiredFieldsEntity()
    {
        $data = self::createPOSTOnlyRequiredData();
        $expectedResult = self::createPOSTOnlyRequiredResponseData();

        $apiOutput = $this->httpPost(['name' => static::$routeName], $data);
        static::assertEquals(Response::HTTP_CREATED, $apiOutput->getStatusCode());
        $result = $apiOutput->getData();
        static::assertArrayHasKey('createdAt', $result);
        static::assertArrayHasKey('updatedAt', $result);
        unset($result['createdAt'], $result['updatedAt']);
        static::assertEquals($expectedResult, $result);

        // GET AFTER POST
        $apiOutput = $this->httpGet(['name' => '{{ route_name_get }}', 'params' => ['id' => $expectedResult['id']]]);
        static::assertEquals(Response::HTTP_OK, $apiOutput->getStatusCode());
        $result = $apiOutput->getData();
        static::assertArrayHasKey('createdAt', $result);
        static::assertArrayHasKey('updatedAt', $result);
        unset($result['createdAt'], $result['updatedAt']);
        static::assertEquals($expectedResult, $result);
    }

    /**
     * POST - without required fields case.
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testPostWithoutRequiredFields()
    {
        $requiredFields = static::getRequiredFields();

        foreach ($requiredFields as $requiredField) {

            $data = self::createPOSTOnlyRequiredData();
            unset($data[$requiredField]);

            $apiOutput = $this->httpPost(['name' => static::$routeName], $data);

            $result = $apiOutput->getData();
            static::assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $apiOutput->getStatusCode());
            static::assertEquals(['errors' => ["core.error.{{ error_context }}.{$requiredField}.required"]], $result);
        }
    }

    /**
     * POST - Error case - 401 - Without authentication.
     */
    public function testPost401()
    {
        $data = self::createPOSTResponseData();
        $apiOutput = $this->httpPost(['name' => static::$routeName], $data, false);
        static::assertApiProblemError($apiOutput, Response::HTTP_UNAUTHORIZED, [ApiProblem::JWT_NOT_FOUND]);
    }

    /**
     * POST - Error case - 403 - Missing create right.
     */
    public function testPostWithoutRightC403()
    {
        $token = self::loginHttp('[API-TESTS-NO-RULES]', 'u-norules-pwd');
        $data = self::createPOSTResponseData();
        $apiOutput = $this->httpPost([
            'name' => static::$routeName,
            'params' => ['id' => self::ENTITY_ID_1]],
            $data,
            false,
            Format::JSON,
            Format::JSON,
            ['Authorization' => self::getAuthorizationTokenPrefix()." {$token}"]
        );

        static::assertApiProblemError($apiOutput, Response::HTTP_FORBIDDEN, [ApiProblem::RESTRICTED_ACCESS]);
    }
}