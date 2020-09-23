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
     * @param array|null $params
     */
    protected function doTestGetList(string $filename = null, array $params = []): void
    {
        $apiOutput = self::httpGet(['name' => static::getGetListRouteName(), 'params' => $params]);

        self::assertEquals(Response::HTTP_OK, $apiOutput->getStatusCode());

        if(null !== $filename) {
            $expectedResult = $this->getExpectedResponse($filename, 'GetList', $apiOutput->getData());
        } else {
            $expectedResult = self::createGETListResponseData();
        }

        static::assertEquals($expectedResult, $apiOutput->getData());
    }

    /**
     * @param string $filename
     * @param int|null $page
     * @param int|null $limit
     * @param array $params
     */
    protected function doTestGetListPaginate(string $filename, int $page = null, int $limit = null, array $params = [])
    {
        try {
            $pagination = [];
            if(null !== $page) {
                $pagination['page'] = $page;
            }
            if(null !== $limit) {
                $pagination['limit'] = $limit;
            }
            $this->doTestGetList($filename, array_merge($pagination, $params));
        } catch (ReflectionException $e) {
            echo $e->getMessage();
        }
    }

    /**
     * @param string $filename
     * @param int|null $page
     * @param int|null $limit
     * @param array $filters
     * @param string|null $sort
     * @param array $params
     */
    protected function doTestGetListFiltered(string $filename, int $page = null, int $limit = null, array $filters = [], string $sort = null, array $params = [])
    {
        $this->doTestGetListPaginate($filename, $page, $limit, array_merge($filters, ['sort' => $sort], $params));
    }

    /**
     * GET - Error case - not found - Without authentication.
     */
    protected function doTestGetListNotFound(): void
    {
        $apiOutput = self::httpGet(['name' => static::getGetListRouteName(), 'params' => []]);
        static::assertApiProblemError($apiOutput, Response::HTTP_NOT_FOUND, [ApiProblem::ENTITY_NOT_FOUND]);
    }

    /**
     * GET - Error case - 401 - Without authentication.
     */
    protected function doTestGetWithoutAuthentication(): void
    {
        $apiOutput = self::httpGet(['name' => static::getGetListRouteName(), 'params' => []], false);
        static::assertApiProblemError($apiOutput, Response::HTTP_UNAUTHORIZED, [ApiProblem::JWT_NOT_FOUND]);
    }

    /**
     * GET - Error case - 403 - Missing right.
     * @param string|null $userLogin
     * @param string|null $userPassword
     */
    protected function doTestGetWithoutRight(string $userLogin = null, string $userPassword = null): void
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
            'name' => static::getGetListRouteName(), 'params' => []],
            false,
            Format::JSON,
            ['Authorization' => self::getAuthorizationTokenPrefix()." {$token}"]
        );

        static::assertApiProblemError($apiOutput, Response::HTTP_FORBIDDEN, [ApiProblem::RESTRICTED_ACCESS]);
    }
}
