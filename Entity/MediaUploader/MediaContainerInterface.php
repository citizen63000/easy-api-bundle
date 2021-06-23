<?php

namespace EasyApiBundle\Entity\MediaUploader;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

interface MediaContainerInterface
{
    /**
     * Return Uuid of entity
     * @return Uuid
     */
    public function getUuid() :UuidInterface;
}
