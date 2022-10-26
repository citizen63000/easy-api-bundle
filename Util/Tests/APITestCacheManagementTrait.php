<?php

namespace EasyApiBundle\Util\Tests;

use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\CacheInterface;

trait APITestCacheManagementTrait
{
    protected static bool $useCache = true;
    protected static ?CacheInterface $cache = null;

    /**
     * Initialize self::$cache.
     */
    protected static function initializeCache(): void
    {
        if (null === static::$cache) {
            static::$cache = static::getContainer()->get('cache.app');
        }
    }

    /**
     * @param $key
     * @return mixed
     * @throws InvalidArgumentException
     */
    protected static function getCachedData($key)
    {
        $key = str_replace(['{', '}', '(', ')', '/', '\\', '@'], '_ESCAPED_', $key);

        try {
            return static::$cache->getItem($key);
        } catch (\Exception $e) {
            return null;
        }
    }
}
