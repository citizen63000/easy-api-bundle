<?php

namespace EasyApiBundle\Util\Tests\crud\functions;

use EasyApiBundle\Util\ApiProblem;
use EasyApiBundle\Util\Tests\ApiOutput;
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
     * @todo dev comment lines
     * @param array $params
     * @param string|null $filename
     * @param string|null $userLogin
     * @param string|null $userPassword
     */
    public function doTestGenericDownload(array $params = [], string $filename = null, string $userLogin = null, string $userPassword = null)
    {
        /** @var ApiOutput $apiOutput */
        $apiOutput = self::httpGetWithLogin(['name' => static::getDownloadRouteName(), 'params' => $params], $userLogin, $userPassword);

        self::assertEquals(Response::HTTP_OK, $apiOutput->getStatusCode());
//        $result = $apiOutput->getData();

        if(null !== $filename) {

            $path = "{$this->getCurrentDir()}/Responses/".self::$downloadActionType."/$filename";

            $expectedHeaders = [
                'Content-Transfer-Encoding' => 'binary',
//                'Content-Type' => mime_content_type($path),
//                'Content-Type' => finfo_buffer(finfo_open(), file_get_contents($path), FILEINFO_MIME_TYPE),
                'Content-Disposition' => "attachment; filename=\"$filename\"",
            ];

            // check file content
//            $expectedResult = $this->getExpectedFileResponse($filename, $result);
//            static::assertEquals($expectedResult, $result, "Assert content failed for file {$filename}");

            // check headers
            foreach ($expectedHeaders as $key => $expectedValue) {
                static::assertEquals($expectedValue, $apiOutput->getHeaderLine($key), "Assert failed for header line '$key'");
            }

        } /*else {
            static::assertTrue(!empty($result),'Empty response, no data returned.');
        }*/
    }
}