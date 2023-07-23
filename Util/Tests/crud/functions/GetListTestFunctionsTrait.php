<?php

namespace EasyApiBundle\Util\Tests\crud\functions;

use EasyApiBundle\Util\ApiProblem;
use EasyApiBundle\Util\Tests\ApiOutput;
use Symfony\Component\HttpFoundation\Response;

trait GetListTestFunctionsTrait
{
    use crudFunctionsTestTrait;

    /**
     * GET LIST - Nominal case.
     * @param string|null $filename
     * @param array|null $params
     * @param string|null $userLogin

     * @return ApiOutput
     */
    protected function doTestGetList(string $filename, array $params = [], string $userLogin = null): ApiOutput
    {
        $apiOutput = self::httpGetWithLogin(['name' => static::getGetListRouteName(), 'params' => $params], $userLogin);

        self::assertEquals(Response::HTTP_OK, $apiOutput->getStatusCode());

        $result = $apiOutput->getData();
        $expectedResult = $this->getExpectedResponse($filename, self::$getListActionType, $apiOutput->getData());
        static::assertAssessableContent($expectedResult, $result);
        static::assertEquals($expectedResult, $result, "Assert failed for file {$filename}");

        return $apiOutput;
    }

    /**
     * @param string $filename
     * @param int|null $page
     * @param int|null $limit
     * @param array $params
     * @param string|null $userLogin

     * @return ApiOutput
     */
    protected function doTestGetListPaginate(string $filename, int $page = null, int $limit = null, array $params = [], string $userLogin = null): ApiOutput
    {
        try {
            $pagination = [];
            if (null !== $page) {
                $pagination['page'] = $page;
            }
            if (null !== $limit) {
                $pagination['limit'] = $limit;
            }
            return $this->doTestGetList($filename, $pagination + $params, $userLogin);
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
     * @param string|null $userLogin

     * @return ApiOutput
     */
    protected function doTestGetListFiltered(string $filename, int $page = null, int $limit = null, array $filters = [], string $sort = null, array $params = [], string $userLogin = null): ApiOutput
    {
        return $this->doTestGetListPaginate($filename, $page, $limit, $filters + ['sort' => $sort] + $params, $userLogin);
    }

    /**
     * GET - Error case - 401 - Without authentication.
     * @return ApiOutput
     */
    protected function doTestGetWithoutAuthentication(): ApiOutput
    {
        $apiOutput = self::httpGet(['name' => static::getGetListRouteName(), 'params' => []], false);
        static::assertApiProblemError($apiOutput, Response::HTTP_UNAUTHORIZED, [ApiProblem::JWT_NOT_FOUND]);

        return $apiOutput;
    }

    /**
     * GET - Error case - 403 - Missing right.
     * @param string|null $userLogin

     * @return ApiOutput
     */
    protected function doTestGetWithoutRight(string $userLogin = null): ApiOutput
    {
        if (null === $userLogin) {
            $userLogin = static::USER_NORULES_TEST_USERNAME;
        }

        $apiOutput = self::httpGetWithLogin(['name' => static::getGetListRouteName(), 'params' => []], $userLogin);

        static::assertApiProblemError($apiOutput, Response::HTTP_FORBIDDEN, [ApiProblem::RESTRICTED_ACCESS]);

        return $apiOutput;
    }
}