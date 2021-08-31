<?php

namespace EasyApiBundle\Util\Tests\crud\functions;

use EasyApiBundle\Util\ApiProblem;
use Symfony\Component\HttpFoundation\Response;

trait DownloadTestFunctionsTrait
{
    use crudFunctionsTestTrait;

    /**
     * GET - Nominal case.
     * @param int|null $id
     * @param string|null $filename
     * @param string|null $userLogin
     * @param string|null $userPassword
     */
    public function doTestDownload(int $id = null, string $filename = null, string $userLogin = null, string $userPassword = null): void
    {
        self::doTestGenericDownload(['id' => $id ?? static::defaultEntityId], $filename, $userLogin, $userPassword);
    }

    /**
     * @param array $params
     * @param string|null $filename
     * @param string|null $userLogin
     * @param string|null $userPassword
     */
    public function doTestGenericDownload(array $params = [], string $filename = null, string $userLogin = null, string $userPassword = null)
    {
        $apiOutput = self::httpGetWithLogin(['name' => static::getDownloadRouteName(), 'params' => $params], $userLogin, $userPassword);

        self::assertEquals(Response::HTTP_OK, $apiOutput->getStatusCode());
        $result = $apiOutput->getData();
//        $expectedResult = $this->getExpectedResponse($filename, static::$downloadActionType, $result);

//        static::assertAssessableContent($expectedResult, $result);
//        static::assertEquals($expectedResult, $result, "Assert failed for file {$filename}");
    }

}
