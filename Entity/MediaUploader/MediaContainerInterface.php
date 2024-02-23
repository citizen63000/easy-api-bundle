<?php

namespace EasyApiBundle\Entity\MediaUploader;

use Ramsey\Uuid\UuidInterface;

interface MediaContainerInterface
{
    /**
     * Return Uuid of entity
     */
    public function getUuid() :?UuidInterface;
}
