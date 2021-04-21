<?php

namespace EasyApiBundle\Services\MediaUploader;

use EasyApiBundle\Entity\MediaUploader\AbstractMedia;
use Psr\Container\ContainerInterface;
use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Naming\NamerInterface;
use Vich\UploaderBundle\Naming\OrignameNamer;

class MediaUploaderFileNamer implements NamerInterface
{
    /** @var ContainerInterface  */
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
     * Returns the name of the file that will be uploaded.
     *
     * @param AbstractMedia $object
     * @param PropertyMapping $mapping
     * @return string
     */
    public function name($object, PropertyMapping $mapping): string
    {
        if ($namer = $object->getFileNamer() && $object->getFileNamer() !== self::class) {
            return $this->container->get($namer)->name($object, $mapping);
        } else {
            return (new OrignameNamer())->name($object, $mapping);
        }
    }
}