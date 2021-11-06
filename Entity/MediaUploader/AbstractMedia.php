<?php

namespace EasyApiBundle\Entity\MediaUploader;

use Doctrine\ORM\Mapping as ORM;
use EasyApiBundle\Entity\AbstractBaseEntity;
use EasyApiBundle\Entity\AbstractBaseUniqueEntity;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Vich\UploaderBundle\Naming\OrignameNamer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\MappedSuperclass
 */
abstract class AbstractMedia extends AbstractBaseUniqueEntity
{
    /** @var string directory namer service to use */
    protected const directoryNamer = null;

    /** @var array  */
    public static array $mimeTypes = [];

    /** @var int|null  */
    public static ?int $maxsize = null;

    /** @var bool */
    public static ?bool $isImage = false;

    /** @var bool|null  */
    public static ?bool $minWidth = null;

    /** @var bool|null  */
    public static ?bool $minHeight = null;

    /** @var bool|null  */
    public static ?bool $maxWidth = null;

    /** @var bool|null  */
    public static ?bool $maxHeight = null;

    /** @var bool|null  */
    public static ?bool $minRatio = null;

    /** @var bool|null  */
    public static ?bool $maxRatio = null;


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
    public static function getMimeTypes(): array
    {
        return static::$mimeTypes;
    }

    /**
     * @return int|null
     */
    public static function getMaxSize(): ?int
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
     * @return bool|null
     */
    public static function getMinWidth(): ?bool
    {
        return self::$minWidth;
    }

    /**
     * @return bool|null
     */
    public static function getMinHeight(): ?bool
    {
        return self::$minHeight;
    }

    /**
     * @return bool|null
     */
    public static function getMaxWidth(): ?bool
    {
        return self::$maxWidth;
    }

    /**
     * @return bool|null
     */
    public static function getMaxHeight(): ?bool
    {
        return self::$maxHeight;
    }

    /**
     * @return bool|null
     */
    public static function getMinRatio(): ?bool
    {
        return self::$minRatio;
    }

    /**
     * @return bool|null
     */
    public static function getMaxRatio(): ?bool
    {
        return self::$maxRatio;
    }

    /**
     * @param ClassMetadata $metadata
     */
    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $assertOptions = [];

        $mimeTypes = static::getMimeTypes();
        if (!empty($mimeTypes)) {
            $assertOptions['mimeTypes'] = $mimeTypes;
        }
        if ($maxSize = static::getMaxSize()) {
            $assertOptions['maxSize'] = $maxSize;
        }

        if (static::$isImage) {
            $optionNames = ['minWidth', 'maxWidth', 'minHeight', 'maxHeight'];
            foreach ($optionNames as $optionName) {
                $method = 'get'.ucfirst($optionName);
                $value = static::$method();
                if (null !== $value) {
                    $assertOptions[$optionName] = $value;
                }
            }
        }

        if (!empty($assertOptions)) {
            $metadata->addPropertyConstraint(static::$isImage ? 'headshot' : 'bioFile', new Assert\File($assertOptions));
        }
    }
}
