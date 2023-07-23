<?php

namespace EasyApiBundle\Util\Tests\crud\functions;

use EasyApiBundle\Util\Tests\ApiOutput;
use Symfony\Component\HttpFoundation\Response;

trait DownloadTestFunctionsTrait
{
    use crudFunctionsTestTrait;

    /**
     * GET - Nominal case.
     * @param int|null $id
     * @param string|null $filename
     * @param string|null $folder
     * @param string|null $userLogin

     */
    public function doTestDownload(int $id = null, string $filename = null, string $folder = null, string $userLogin = null): void
    {
        self::doTestGenericDownload(['id' => $id ?? static::defaultEntityId], $filename, $folder, $userLogin);
    }

    /**
     * @param array $params
     * @param string|null $filename
     * @param string|null $folder
     * @param string|null $userLogin

     * @todo dev comment lines
     */
    public function doTestGenericDownload(array $params = [], string $filename = null, string $folder = null, string $userLogin = null)
    {
        if(null !== $filename && null !== $folder) {
            $src = self::$projectDir."/tests/artifacts/$folder/$filename";
            $destDir = self::$projectDir."/$folder";
            if(!file_exists($destDir)) {
                mkdir($destDir, 0755, true);
            }
            copy($src, "$destDir/$filename");
        }

        /** @var ApiOutput $apiOutput */
        $apiOutput = self::httpGetWithLogin(['name' => static::getDownloadRouteName(), 'params' => $params], $userLogin);

        self::assertEquals(Response::HTTP_OK, $apiOutput->getStatusCode());
//        $result = $apiOutput->getData();
        if(null !== $filename) {

//            $path = "{$this->getCurrentDir()}/Responses/".self::$downloadActionType."/$filename";

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