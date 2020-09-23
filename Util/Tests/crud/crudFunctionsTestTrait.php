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
     * @param bool $dateProtection
     * @return array
     */
    protected function getExpectedResponse(string $filename, string $type, array $result, bool $dateProtection = false): array
    {
        $dir = $this->getCurrentDir()."/Responses/{$type}";
        $filePath = "{$dir}/{$filename}";

        if(!file_exists($filePath)) {
            if(!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }

            // created_at / updated_at fields
            if($dateProtection) {
                if('Create' === $type) {
                    if(array_key_exists('createdAt', $result)) {
                        $result['createdAt'] = '\assertDateTime()';
                    }
                }
                if(array_key_exists('updatedAt', $result)) {
                    $result['updatedAt'] = '\assertDateTime()';
                }
            }

            file_put_contents($filePath, json_encode($result, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_FORCE_OBJECT));
        }

        return json_decode(file_get_contents($filePath), true);
    }

    /**
     * @param string $filename
     * @param string $type Create|Update
     * @param string $defaultContent
     * @return array
     * @throws \Exception
     */
    protected function getDataSent(string $filename, string $type, string $defaultContent = '{}'): array
    {
        $dir = $this->getCurrentDir()."/DataSent/{$type}";
        $filePath = "{$dir}/{$filename}";

        if(!file_exists($filePath)) {
            if(!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
            file_put_contents($filePath, $defaultContent);
        }

        if($json = json_decode(file_get_contents($filePath), true)) {
            return $json;
        }

        throw new \Exception("Invalid json in file {$filename}");
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
