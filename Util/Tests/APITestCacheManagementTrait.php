<?php

namespace EasyApiBundle\Util\Tests;

use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\Adapter\AbstractAdapter;

trait APITestCacheManagementTrait
{
    protected static bool $useCache = true;
    protected static ?AbstractAdapter $cache = null;

    /**
     * Initialize self::$cache.
     */
    protected static function initializeCache(): void
    {
        if (null === self::$cache) {
            self::$cache = self::$container->get('cache.app');
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
