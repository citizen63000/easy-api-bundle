<?php

namespace EasyApiBundle\Entity\MediaUploader;

use Doctrine\ORM\Mapping as ORM;
use EasyApiBundle\Entity\AbstractBaseEntity;
use EasyApiBundle\Entity\AbstractBaseUniqueEntity;
use EasyApiBundle\Services\MediaUploader\FileManager;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Serializer\Annotation\Groups;
use Vich\UploaderBundle\Naming\OrignameNamer;

/**
 * @ORM\MappedSuperclass
 */
abstract class AbstractMedia extends AbstractBaseUniqueEntity
{
    /** @var string directory namer service to use */
    protected const directoryNamer = null;

    /** @var array  */
    public static array $mimeTypes = [];

    /** @var string|null  */
    public static ?string $maxsize = null;

    /** @var bool */
    public static bool $isImage = false;

    /** @var int|null  */
    public static ?int $minWidth = null;

    /** @var int|null  */
    public static ?int $minHeight = null;

    /** @var int|null  */
    public static ?int $maxWidth = null;

    /** @var int|null  */
    public static ?int $maxHeight = null;

    /** @var int|null  */
    public static ?int $minRatio = null;

    /** @var int|null  */
    public static ?int $maxRatio = null;

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
    private ?string $filename = null;

    /**
     * @var string
     * @ORM\Column(name="original_filename", type="string", length=255, nullable=true)
     * @Groups({"abstract_media_full", "abstract_media_original_filename"})
     */
    private ?string $originalFilename = null;

    /** @var File|null */
    private ?File $file = null;

    /** @var string|null */
    private ?string $directoryName = null;

    /** @var string|null */
    private ?string $directoryValue = null;

    /**
     * @var AbstractBaseEntity
     * @ORM\JoinColumns(@ORM\JoinColumn(name="container_entity_id", referencedColumnName="id"))
     * @Groups({"abstract_media_full", "abstract_media_container_entity"})
     */
    protected $containerEntity;

    /**
     * @var FileManager|null
     */
    protected ?FileManager $fileManager = null;

    /**
     * @param FileManager $fileManager
     */
    public function setFileManager(FileManager $fileManager) {
        $this->fileManager = $fileManager;
    }

    /**
     * Clone entity by cloning file too.
     */
    public function __clone()
    {
        parent::__clone();

        if (null === $this->originalFilename) {
            $this->originalFilename = $this->getClonedFilename();
        }

        if (null !== $this->fileManager) {

            $tmpFilePath = '/tmp/' . md5(uniqid());
            $fileData = file_get_contents($this->fileManager->getFileSystemPath($this));
            (new Filesystem())->dumpFile($tmpFilePath, $fileData);
            $mimeType = finfo_buffer(finfo_open(), $fileData, FILEINFO_MIME_TYPE);

            $this->setFile(new UploadedFile($tmpFilePath, $this->getClonedFilename(), $mimeType, null, true));
        }
    }

    /**
     * @return string|null
     */
    protected function getClonedFilename(): ?string
    {
        if (null !== $this->originalFilename) {
            return $this->getOriginalFilename();
        } elseif (OrignameNamer::class === static::fileNamer) {
            return preg_replace('/^[a-zA-Z0-9]+_/', '', $this->getFilename());
        }

        return $this->getFilename();
    }

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
     * @return string|null
     */
    public function getOriginalFilename(): ?string
    {
        return $this->originalFilename;
    }

    /**
     * @param string|null $originalFilename
     */
    public function setOriginalFilename(?string $originalFilename): void
    {
        $this->originalFilename = $originalFilename;
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
    public static function getMimeTypes(): array
    {
        return static::$mimeTypes;
    }

    /**
     * @return string|null
     */
    public static function getMaxSize(): ?string
    {
        return static::$maxsize;
    }

    /**
     * @return bool
     */
    public static function isImage(): ?bool
    {
        return self::$isImage;
    }

    /**
     * @return int|null
     */
    public static function getMinWidth(): ?int
    {
        return self::$minWidth;
    }

    /**
     * @return int|null
     */
    public static function getMinHeight(): ?int
    {
        return self::$minHeight;
    }

    /**
     * @return int|null
     */
    public static function getMaxWidth(): ?int
    {
        return self::$maxWidth;
    }

    /**
     * @return int|null
     */
    public static function getMaxHeight(): ?int
    {
        return self::$maxHeight;
    }

    /**
     * @return int|null
     */
    public static function getMinRatio(): ?int
    {
        return self::$minRatio;
    }

    /**
     * @return int|null
     */
    public static function getMaxRatio(): ?int
    {
        return self::$maxRatio;
    }
}
