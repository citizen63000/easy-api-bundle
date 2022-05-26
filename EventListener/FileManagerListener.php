<?php

namespace EasyApiBundle\EventListener;

use EasyApiBundle\Services\MediaUploader\FileManager;
use Doctrine\ORM\Event\LifecycleEventArgs;

class FileManagerListener
{
    private FileManager $fileManager;

    /**
     * FileManagerListener constructor.
     * @param FileManager $fileManager
     */
    public function __construct(FileManager $fileManager) {
        $this->fileManager = $fileManager;
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        // @todo compare instance instead of method exists
        if (method_exists($entity, 'setFileManager')) {
            $entity->setFileManager($this->fileManager);
        }
    }
}