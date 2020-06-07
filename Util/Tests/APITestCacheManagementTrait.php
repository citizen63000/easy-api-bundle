<?php

namespace EasyApiBundle\Util\Tests;

use Symfony\Component\Cache\Adapter\AbstractAdapter;

trait APITestCacheManagementTrait
{
    /**
     * @var bool
     */
    protected static $useCache = true;

    /**
     * @var AbstractAdapter
     */
    protected static $cache;

    /**
     *
     */
    protected static function initializeCache() :void
    {
        if(null === self::$cache) {
            self::$cache = self::$container->get('cache.app');
        }
    }


    /**
     * @param $key
     * @return mixed
     */
    protected static function getCachedData($key)
    {
        $key = str_replace(['{', '}', '(', ')', '/', '\\', '@'], '_ESCAPED_', $key);

        return self::$cache->getItem($key);
    }

}