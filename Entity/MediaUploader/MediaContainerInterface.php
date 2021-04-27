<?php

namespace EasyApiBundle\Entity\MediaUploader;

use Ramsey\Uuid\Uuid;

interface MediaContainerInterface
{
    /**
     * Return Uuid of entity
     * @return Uuid
     */
    public function getUuid() :Uuid;
}
