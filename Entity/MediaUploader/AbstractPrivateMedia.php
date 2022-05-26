<?php

namespace EasyApiBundle\Entity\MediaUploader;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\MappedSuperclass
 * @Vich\Uploadable
 */
abstract class AbstractPrivateMedia extends AbstractMedia
{
    protected const downloadRouteName = null;

    /**
     * @Vich\UploadableField(mapping="private_media_uploader", fileNameProperty="filename")
     *
     * @var File|string
     */
    private $file;

    /**
     * @return string|null
     */
    public static function getDownloadRouteName(): ?string
    {
        return !empty(static::downloadRouteName) ? static::downloadRouteName.'_download' : null;
    }
}
