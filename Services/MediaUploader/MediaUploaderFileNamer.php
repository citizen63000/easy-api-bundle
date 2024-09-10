<?php

namespace EasyApiBundle\Services\MediaUploader;

use EasyApiBundle\Entity\MediaUploader\AbstractMedia;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Naming\ConfigurableInterface;
use Vich\UploaderBundle\Naming\NamerInterface;
use Vich\UploaderBundle\Naming\OrignameNamer;
use Vich\UploaderBundle\Naming\PropertyNamer;
use Vich\UploaderBundle\Util\Transliterator;

class MediaUploaderFileNamer implements NamerInterface
{
    protected ContainerInterface $container;

    protected SluggerInterface $slugger;

    public function __construct(ContainerInterface $container, SluggerInterface $slugger)
    {
        $this->container = $container;
        $this->slugger = $slugger;
    }

    /**
     * Returns the name of the file that will be uploaded.
     *
     * @param AbstractMedia $object
     * @param PropertyMapping $mapping
     * @return string
     */
    public function name($object, PropertyMapping $mapping): string
    {
        $namerClassName = $object->getFileNamer();
        $name = null;

        if(null !== $namerClassName) {
            if(self::isVichNamer($namerClassName)) {
                // Vich namer
                $name = self::getVichNamer($namerClassName)->name($object, $mapping);
            } elseif($object->getFileNamer() !== self::class) {
                // custom service namer
                $name = $this->container->get($namerClassName)->name($object, $mapping);
            } else {
                // default name
                $name = (new OrignameNamer())->name($object, $mapping);
            }
        }

        return $name;
    }

    /**
     * @param string $namerClassName
     * @return bool
     */
    private function isVichNamer(string $namerClassName): bool
    {
        return substr($namerClassName, 0, 4) === 'Vich';
    }

    /**
     * @param string $namerClassName
     * @return NamerInterface
     */
    private function getVichNamer(string $namerClassName): NamerInterface
    {
        /** @var NamerInterface|ConfigurableInterface $namer */
        $namer = new $namerClassName(new Transliterator($this->slugger));

        if(PropertyNamer::class === $namerClassName) {
            $namer->configure(['property' => 'generateFileName']);
        }

        return $namer;
    }
}