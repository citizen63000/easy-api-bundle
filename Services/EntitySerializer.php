<?php

namespace EasyApiBundle\Services;

use EasyApiBundle\Entity\AbstractBaseEntity;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\CacheItem;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;

class EntitySerializer
{
    protected const CACHE_NAME_PREFIX = 'easySerializerCache.';
    protected SerializerInterface $serializer;
    protected AdapterInterface $cache;

    /**
     * EntitySerializer constructor.
     * @param SerializerInterface $serializer
     * @param AdapterInterface $cache
     */
    public function __construct(SerializerInterface $serializer, AdapterInterface $cache)
    {
        $this->serializer = $serializer;
        $this->cache = $cache;
    }

    /**
     * @param AbstractBaseEntity $entity
     * @param array $context
     * @param string $format
     * @param bool $useCache
     * @param bool $forceCacheReload
     * @return string
     */
    public function serializeEntity(AbstractBaseEntity $entity, array $context, string $format = JsonEncoder::FORMAT, bool $useCache = false, bool $forceCacheReload = false): string
    {
        try {
            if ($useCache && is_object($entity)) {
                $cachedContent = $this->cache->getItem(static::getSerializerCacheName($entity));
                if (!$cachedContent->isHit() || $forceCacheReload) {
                    $serializedEntity = $this->initCache($entity, $context, $format, $cachedContent);
                } else {
                    $serializedEntity = $cachedContent->get();
                }
            } else {
                $serializedEntity = $this->serializer->serialize($entity, $format, $context);
            }
        } catch (\Exception | InvalidArgumentException $e) {
            $serializedEntity = $this->serializer->serialize($entity, $format, $context);
        }

        return $serializedEntity;
    }

    /**
     * @param AbstractBaseEntity $entity
     * @return string
     */
    public static function getSerializerCacheName(AbstractBaseEntity $entity): string
    {
        return self::CACHE_NAME_PREFIX.str_replace(['\\', 'Proxies___CG___'], ['_', ''], get_class($entity)).".{$entity->getId()}";
    }

    /**
     * @param AbstractBaseEntity $entity
     * @param array $context
     * @param string|null $format
     * @param CacheItem|null $item
     * @return string
     * @throws InvalidArgumentException
     */
    public function initCache(AbstractBaseEntity $entity, array $context, string $format = JsonEncoder::FORMAT, CacheItem $item = null): string
    {
        $item = $item ?? $this->cache->getItem(static::getSerializerCacheName($entity));
        $serializedEntity = $this->serializer->serialize($entity, $format, $context);
        $item->set($serializedEntity);
        $this->cache->save($item);

        return $serializedEntity;
    }

    /**
     * @param AbstractBaseEntity $entity
     * @throws InvalidArgumentException
     */
    public function clearCache(AbstractBaseEntity $entity)
    {
        $this->cache->deleteItem(static::getSerializerCacheName($entity));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function clearAllCaches()
    {
        $this->cache->clear(self::CACHE_NAME_PREFIX);
    }
}