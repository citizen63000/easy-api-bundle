<?php

namespace EasyApiBundle\Entity\MediaUploader;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\MappedSuperclass
 * @Vich\Uploadable
 */
abstract class AbstractPublicMedia extends AbstractMedia
{
    /**
     * @Vich\UploadableField(mapping="public_media_uploader", fileNameProperty="filename")
     *
     * @var File|string
     */
    private $file;
}
