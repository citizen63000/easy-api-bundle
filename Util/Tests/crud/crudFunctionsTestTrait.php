<?php

namespace EasyApiBundle\Util\Tests\crud;

use ReflectionException;

trait crudFunctionsTestTrait
{
    /**
     * @return false|string|string[]
     */
    protected function getCurrentDir()
    {
        try {
            $rc = new \ReflectionClass($this);
            return str_replace('/'.$rc->getShortName().'.php', '', $rc->getFilename());
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * @param string $filename
     * @param string $type Get|GetList|Create|Update
     * @param array $result result in json
     * @return array
     */
    protected function getExpectedResponse(string $filename, string $type, array $result): array
    {
        $dir = $this->getCurrentDir()."/Responses/{$type}";
        $filePath = "{$dir}/{$filename}";

        if(!file_exists($filePath)) {
            if(!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
            file_put_contents($filePath, json_encode($result, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
        }

        return json_decode(file_get_contents($filePath), true);
    }

    /**
     * @param string $filename
     * @param string $type Create|Update
     * @return array
     */
    protected function getDataSent(string $filename, string $type): array
    {
        $dir = $this->getCurrentDir()."/DataSent/{$type}";
        $filePath = "{$dir}/{$filename}";

        if(!file_exists($filePath)) {
            if(!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
            file_put_contents($filePath, '{}');
        }

        return json_decode(file_get_contents($filePath), true);
    }

    /**
     * @return string
     */
    protected static function getGetRouteName()
    {
        return static::baseRouteName.'_get';
    }

    /**
     * @return string
     */
    protected static function getGetListRouteName()
    {
        return static::baseRouteName.'_list';
    }

    /**
     * @return string
     */
    protected static function getCreateRouteName()
    {
        return static::baseRouteName.'_create';
    }

    /**
     * @return string
     */
    protected static function getUpdateRouteName()
    {
        return static::baseRouteName.'_update';
    }

    /**
     * @return string
     */
    protected static function getDeleteRouteName()
    {
        return static::baseRouteName.'_delete';
    }

    /**
     * @return string
     */
    protected static function getDescribeFormRouteName()
    {
        return static::baseRouteName.'_describe_form';
    }
}
