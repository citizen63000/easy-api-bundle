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

        if(null !== $filename) {
            $expectedResult = $this->getExpectedFileResponse($filename, $result);
            $path = "{$this->getCurrentDir()}/Responses/".self::$downloadActionType."/$filename";
//            static::assertEquals(mime_content_type($path), $apiOutput->getHeader('Content-Type'));
//            static::assertEquals('binary', $apiOutput->getHeader('Content-Transfer-Encoding'));
//            static::assertEquals(filesize($path), $apiOutput->getHeader('Content-Length'));
            static::assertEquals($expectedResult, $result, "Assert content failed for file {$filename}");
        } else {
            static::assertTrue(!empty($result) > 0,'Empty response, no data returned.');
        }
    }
}