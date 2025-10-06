<?php

namespace EasyApiBundle\Entity\MediaUploader;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[Vich\Uploadable]
#[ORM\MappedSuperclass]
abstract class AbstractPrivateMedia extends AbstractMedia
{
    protected const ?string downloadRouteName = null;

    #[Vich\UploadableField(mapping: 'private_media_uploader', fileNameProperty: 'filename')]
    protected ?File $file;

    /**
     * @return string|null
     */
    public static function getDownloadRouteName(): ?string
    {
        return !empty(static::downloadRouteName) ? static::downloadRouteName.'_download' : null;
    }
}
