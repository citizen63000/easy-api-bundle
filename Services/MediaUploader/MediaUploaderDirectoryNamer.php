<?php

namespace EasyApiBundle\Services\MediaUploader;

use EasyApiBundle\Entity\MediaUploader\AbstractMedia;
use Psr\Container\ContainerInterface;
use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Naming\DirectoryNamerInterface;

class MediaUploaderDirectoryNamer implements DirectoryNamerInterface
{
    /** @var ContainerInterface */
    protected $container;

    /**
     * DirectoryNamer constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @todo type parameters for php7.4
     *
     * Returns the name of a directory where files will be uploaded.
     *
     * @param AbstractMedia $object
     * @param PropertyMapping $mapping
     *
     * @return string
     */
    public function directoryName($object, PropertyMapping $mapping): string
    {
        if ($directoryNamer = $object->getDirectoryNamer()) {
            return $this->container->get($directoryNamer)->directoryName($object, $mapping);
        }

        if ($directoryName = $object->getDirectoryName()) {
            $pathParameter = $this->container->getParameter("media_uploader_directories_{$directoryName}");
            $this->evalPath($pathParameter, $object);
        }

        return $this->evalPath($object->getDirectoryValue(), $object);
    }

    /**
     * @param string|null $path
     * @param AbstractMedia $media
     * @return string
     */
    private function evalPath(?string $path, AbstractMedia $media): string
    {
        return null != $path ? str_replace(['%container_id%', '%object_id%'], [$media->getContainerEntity()->getUuid(), $media->getUuid()], $path): '';
    }
}
