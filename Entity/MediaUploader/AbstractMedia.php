<?php

namespace EasyApiBundle\Entity\MediaUploader;

use Doctrine\ORM\Mapping as ORM;
use EasyApiBundle\Entity\AbstractBaseEntity;
use EasyApiBundle\Entity\AbstractBaseUniqueEntity;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Groups;
use Vich\UploaderBundle\Naming\OrignameNamer;

/**
 * @ORM\MappedSuperclass
 */
abstract class AbstractMedia extends AbstractBaseUniqueEntity
{
    /** @var string directory namer service to use */
    protected const directoryNamer = null;

    public static $mimeTypes = [];

    /**
     * File namer to use : custom service or Vich namer
     * @see Vich namers : https://github.com/dustin10/VichUploaderBundle/blob/master/docs/namers.md
     * @var string
     */
    protected const fileNamer = OrignameNamer::class;

    /**
     * @var Uuid
     * @ORM\Column(name="uuid", type="uuid", length=255, nullable=false)
     * @Groups({"abstract_media_full", "abstract_media_short", "abstract_media_uuid"})
     */
    protected $uuid;

    /**
     * @var string
     * @ORM\Column(name="filename", type="string", length=255, nullable=true)
     * @Groups({"abstract_media_full", "abstract_media_short", "abstract_media_filename"})
     */
    private $filename;

    /** @var File */
    private $file;

    /** @var string */
    private $directoryName;

    /** @var string */
    private $directoryValue;

    /**
     * @var AbstractBaseEntity
     * @ORM\JoinColumns(@ORM\JoinColumn(name="container_entity_id", referencedColumnName="id"))
     * @Groups({"abstract_media_full", "abstract_media_container_entity"})
     */
    protected $containerEntity;

    /**
     * @return string
     */
    public function getFilename(): ?string
    {
        return $this->filename;
    }

    /**
     * @param string|null $filename
     */
    public function setFilename(?string $filename): void
    {
        $this->filename = $filename;
    }

    /**
     * @return File
     */
    public function getFile(): ?File
    {
        return $this->file;
    }

    /**
     * @param File|null $file
     */
    public function setFile(?File $file): void
    {
        $this->file = $file;
    }

    /**
     * @return string
     */
    public function getDirectoryName(): ?string
    {
        return $this->directoryName;
    }

    /**
     * @param string $directoryName
     */
    public function setDirectoryName(string $directoryName): void
    {
        $this->directoryName = $directoryName;
    }

    /**
     * @return string
     */
    public function getDirectoryValue(): ?string
    {
        return $this->directoryValue;
    }

    /**
     * @param string $directoryValue
     */
    public function setDirectoryValue(string $directoryValue): void
    {
        $this->directoryValue = $directoryValue;
    }

    /**
     * @return MediaContainerInterface
     */
    public function getContainerEntity(): MediaContainerInterface
    {
        return $this->containerEntity;
    }

    /**
     * @param MediaContainerInterface $containerEntity
     */
    public function setContainerEntity(MediaContainerInterface $containerEntity): void
    {
        $this->containerEntity = $containerEntity;
    }

    /**
     * @return string
     */
    public function getDirectoryNamer(): ?string
    {
        return static::directoryNamer;
    }

    /**
     * @return string
     */
    public function getFileNamer(): ?string
    {
        return static::fileNamer;
    }

    /**
     * Implement this if you want to use vich file namer "PropertyNamer"
     * @return string
     */
    public function generateFileName(): string
    {
        return 'you_must_implement_generateFileName_method';
    }

    /**
     * @return array
     */
    public function getMimeTypes(): array
    {
        return static::$mimeTypes;
    }
}
