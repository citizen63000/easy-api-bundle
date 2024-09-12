<?php

namespace EasyApiBundle\Serializer;

use Psr\Container\ContainerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

abstract class AbstractObjectSerializer implements NormalizerInterface, ServiceSubscriberInterface
{
    protected ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getObjectNormalizer(): NormalizerInterface
    {
        return $this->container->get(ObjectNormalizer::class);
    }

    /**
     * @throws ExceptionInterface
     */
    public function normalize(mixed $object, $format = null, array $context = []): float|array|\ArrayObject|bool|int|string|null
    {
        return $this->getObjectNormalizer()->normalize($object, null, $context);
    }

    abstract public function supportsNormalization($data, $format = null);

    /**
     * @return array|string[]
     */
    public static function getSubscribedServices(): array
    {
        return [ObjectNormalizer::class];
    }
}
