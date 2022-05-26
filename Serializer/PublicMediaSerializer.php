<?php

namespace EasyApiBundle\Serializer;

use EasyApiBundle\Entity\MediaUploader\AbstractPublicMedia;
use EasyApiBundle\Serializer\AbstractObjectSerializer;
use Psr\Container\ContainerInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Vich\UploaderBundle\Storage\StorageInterface;

class PublicMediaSerializer extends AbstractObjectSerializer
{
    private StorageInterface $storage;

    /**
     * SerializationListenerFeature constructor.
     * @param ContainerInterface $container
     * @param StorageInterface $storage
     */
    public function __construct(ContainerInterface $container, StorageInterface $storage)
    {
        parent::__construct($container);
        $this->storage = $storage;
    }

    /**
     * @param AbstractPublicMedia $object
     * @param string|null $format
     * @param array $context
     * @return array
     * @throws ExceptionInterface
     */
    public function normalize($object, $format = null, array $context = []): array
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