<?php

namespace EasyApiBundle\Entity\MediaUploader;

use Ramsey\Uuid\UuidInterface;

interface MediaContainerInterface
{
    /**
     * @return UuidInterface
     */
    public function getUuid() :UuidInterface;
}
