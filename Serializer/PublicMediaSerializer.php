<?php

namespace EasyApiBundle\Serializer;

use EasyApiBundle\Entity\MediaUploader\AbstractPublicMedia;
use Psr\Container\ContainerInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Vich\UploaderBundle\Storage\StorageInterface;

class PublicMediaSerializer extends AbstractObjectSerializer
{
    private StorageInterface $storage;

    public function __construct(ContainerInterface $container, StorageInterface $storage)
    {
        parent::__construct($container);
        $this->storage = $storage;
    }

    /**
     * @throws ExceptionInterface
     */
    public function normalize(mixed $object, $format = null, array $context = []): float|array|\ArrayObject|bool|int|string|null
    {
        $data = parent::normalize($object, $format, $context);

        if ($object->getFilename()) {
            $data['fileUrl'] = $this->storage->resolveUri($object, 'file');
        }

        return $data;
    }

    /**
     * @inheritDoc
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof AbstractPublicMedia;
    }
}
