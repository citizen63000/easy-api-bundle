<?php

namespace EasyApiBundle\Model;

use EasyApiBundle\Util\StringUtils\Inflector;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Faker\Factory as FakerFactory;

class EntityField
{
    protected EntityConfiguration $entity;

    protected ?string $name = null;

    protected ?string $tableColumnName = null;

    protected ?string $referencedColumnName = null;

    protected ?string $inverseTableColumnName = null;

    protected ?string $inverseReferencedColumnName = null;

    protected ?string $joinTable = null;

    protected ?string $joinTableSchema;

    protected bool $isPrimary = false;

    protected bool $isNativeType = true;

    protected ?string $nativeType = null;

    protected ?string $type = null;

    protected ?string $entityType = null;

    protected ?string $relationType;

    protected bool $isRequired = false;

    protected bool $isAutoIncremented = false;

    /**
     * @var mixed
     */
    protected $default;

    protected ?int $length;

    /**
     * Precision in case of float type.
     */
    protected ?int $precision;

    /**
     * Scale in case of float type.
     */
    protected ?int $scale;

    /**
     * @var mixed
     */
    protected $randomValue;

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getName();
    }

    public function getEntity(): EntityConfiguration
    {
        return $this->entity;
    }

    public function setEntity(EntityConfiguration $entity): void
    {
        $this->entity = $entity;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getTableColumnName(): ?string
    {
        if (null !== $this->tableColumnName) {
            return $this->tableColumnName;
        }

        $converter = new CamelCaseToSnakeCaseNameConverter();

        if ($this->isNativeType()) {
            return $converter->normalize($this->getName());
        }

        return $converter->normalize($this->getName()).'_id';
    }

    public function setTableColumnName(string $tableColumnName): void
    {
        $this->tableColumnName = $tableColumnName;
    }

    public function getReferencedColumnName(): ?string
    {
        return $this->referencedColumnName;
    }

    public function setReferencedColumnName(string $referencedColumnName): void
    {
        $this->referencedColumnName = $referencedColumnName;
    }

    public function getInverseTableColumnName(): ?string
    {
        return $this->inverseTableColumnName;
    }

    public function setInverseTableColumnName(?string $inverseTableColumnName): void
    {
        $this->inverseTableColumnName = $inverseTableColumnName;
    }

    public function getInverseReferencedColumnName(): ?string
    {
        return $this->inverseReferencedColumnName;
    }

    public function setInverseReferencedColumnName(?string $inverseReferencedColumnName): void
    {
        $this->inverseReferencedColumnName = $inverseReferencedColumnName;
    }

    public function getJoinTable(): string
    {
        return $this->joinTable;
    }

    public function setJoinTable(string $joinTable): void
    {
        $this->joinTable = $joinTable;
    }

    public function getJoinTableSchema(): string
    {
        return $this->joinTableSchema;
    }

    public function setJoinTableSchema(string $joinTableSchema): void
    {
        $this->joinTableSchema = $joinTableSchema;
    }

    public function isPrimary(): bool
    {
        return $this->isPrimary;
    }

    public function setIsPrimary(bool $isPrimary): void
    {
        $this->isPrimary = $isPrimary;
    }

    public function isNativeType(): bool
    {
        return $this->isNativeType;
    }

    public function setIsNativeType(bool $isNativeType): void
    {
        $this->isNativeType = $isNativeType;
    }

    public function getNativeType(): string
    {
        return $this->nativeType;
    }

    public function setNativeType(string $nativeType): void
    {
        $this->nativeType = $nativeType;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getEntityType(): ?string
    {
        return $this->entityType;
    }

    public function setEntityType(string $entityType): void
    {
        $this->entityType = $entityType;
    }

    public function getRelationType(): ?string
    {
        return $this->relationType;
    }

    public function setRelationType(string $relationType): void
    {
        $this->relationType = $relationType;
    }

    public function isRequired(): bool
    {
        return $this->isRequired;
    }

    public function setIsRequired(bool $isRequired)
    {
        $this->isRequired = $isRequired;
    }

    public function isAutoIncremented(): bool
    {
        return $this->isAutoIncremented;
    }

    public function setIsAutoIncremented(bool $isAutoIncremented): void
    {
        $this->isAutoIncremented = $isAutoIncremented;
    }

    /**
     * @return mixed
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * @param mixed $default
     */
    public function setDefault($default)
    {
        $this->default = $default;
    }

    /**
     * @return bool|mixed|null
     */
    public function getDefaultValue()
    {
        if ('boolean' === $this->getType()) {
            return (bool) $this->getDefault();
        } elseif ('datetime' === $this->getType()) {
            return null;
        }

        return $this->getDefault();
    }

    public function getLength(): ?int
    {
        return $this->length;
    }

    public function setLength(?int $length): void
    {
        $this->length = $length;
    }

    public function getPrecision(): int
    {
        return $this->precision;
    }

    public function setPrecision(?int $precision): void
    {
        $this->precision = $precision;
    }

    public function getScale(): int
    {
        return $this->scale;
    }

    public function setScale(?int $scale): void
    {
        $this->scale = $scale;
    }

    public function getGetterName(): string
    {
        return 'boolean' === $this->getType() ? ('is' === substr($this->name, 0, 2) ? $this->name : 'is'.ucfirst($this->name)) : 'get'.ucfirst($this->name);
    }

    public function getSetterName(): string
    {
        return 'set'.ucfirst($this->name);
    }

    public function getAdderName(): ?string
    {
        if ('array' === $this->getType() || '\Doctrine\Common\Collections\ArrayCollection' === $this->getType()) {
            return 'add'.ucfirst(Inflector::singularize($this->getName()));
        }

        return null;
    }

    public function getRemoverName(): ?string
    {
        if ('array' === $this->getType() || '\Doctrine\Common\Collections\ArrayCollection' === $this->getType()) {
            return 'remove'.ucfirst(Inflector::singularize($this->getName()));
        }

        return null;
    }

    /**
     * Get type for the serializer.
     */
    public function getSerializerType(): ?string
    {
        if ('\Doctrine\Common\Collections\ArrayCollection' === $this->getType()) {
            return 'ArrayCollection<'.$this->getEntityType().'>';
        }

        if ('date' === strtolower($this->getType())) {
            return 'DateTime<\'Y-m-d\'>';
        }

        if ('datetime' === strtolower($this->getType())) {
            return 'DateTime<\'Y-m-d H:i:s\'>';
        }

        if ('uuid' === strtolower($this->getType())) {
            return 'string';
        }

        if ($this->isNativeType()) {

            if ('text' === $this->getType()) {
                return 'string';
            }

            return $this->getType();
        }

        return $this->getEntityType();
    }

    /**
     * @return int|mixed|string
     * @throws \Exception
     */
    public function getRandomValue(bool $forceNew = false): mixed
    {
        if (null === $this->randomValue || $forceNew) {
            $this->randomValue = $this->generateRandomValue();
        }

        return $this->randomValue;
    }

    public function setRandomValue($value): void
    {
        $this->randomValue = $value;
    }

    /**
     * @throws \Exception
     */
    protected function generateRandomValue(string $lang = FakerFactory::DEFAULT_LOCALE): array|float|UuidInterface|bool|int|string|null
    {
        $faker = FakerFactory::create($lang);

        if ($this->isNativeType() && !$this->isPrimary()) {
            if ('date' === strtolower($this->getType())) {
                return $faker->dateTime()->format('Y-m-d');
            }

            if ('datetime' === strtolower($this->getType())) {
                if ('updatedAt' === $this->getName()) {
                    return (new \DateTime())->format('Y-m-d H:i:s');
                }
                return $faker->dateTime()->format('Y-m-d H:i:s');
            }

            if ('boolean' === $this->getType()) {
                return (bool) random_int(0, 1);
            }

            if ('integer' === $this->getType()) {
                return $this->uniqueRandomNumbersWithinRange(0, 5000);
            }

            if ('float' === $this->getType()) {
                $scale = ($this->scale == 0) ? 1 : $this->scale;
                $precision = ($this->precision == 0) ? 2 : $this->precision;
                return random_int(1, (2 ** ($precision - 1)) * (2 ** $scale)) / (2 ** $scale);
            }

            if ('string' === $this->getType()) {
                if ('siren' == $this->getName() || 'siret' == $this->getName()) {
                    $func = $this->getName();
                    return str_replace(' ', '', (FakerFactory::create('fr_FR'))->$func());
                }
                if (in_array($this->getName(), ['email', 'text'])) {
                    $func = $this->getName();
                    return $faker->$func();
                }
                if ('firstname' === mb_strtolower($this->getName())) {
                    return $faker->firstName();
                }
                if ('lastname' === mb_strtolower($this->getName())) {
                    return $faker->lastName();
                }
                if ('name' === mb_strtolower($this->getName())) {
                    return $faker->text($this->getLength() ?? 255);
                }
                if ($this->getLength() > 255) {
                    $faker->realText($this->getLength());
                }

                return $faker->text($this->getLength() ?? 255);
            }

            if('text' === $this->getType()) {
                if ($this->getLength() > 255) {
                    $faker->text($faker->numberBetween(256, $this->getLength()));
                }
            }

            if ('uuid' === $this->getType()) {
                return Uuid::uuid4();
            }

        } elseif ($this->isPrimary()) {
            return '1';
        } else {

            if ($this->isReferential()) {
                return ['code' => 'the_code', 'name' => 'the name'];
            }

            return '1';
        }

        return null;
    }

    /**
     * Generate random integer with range.
     */
    protected function uniqueRandomNumbersWithinRange(int $min, int $max): array
    {
        $numbers = range($min, $max);
        shuffle($numbers);

        return array_slice($numbers, 0, 1)[0];
    }

    public function setTypeFromMysqlType(string $dbType): string
    {
        if (in_array(strtolower($dbType), ['tinyint(1)', 'bool', 'boolean'])) {
            $this->setType('boolean');
        } elseif (preg_match('/int\(([0-9]+)\)/', $dbType, $matches)) {
            $this->setType('integer');
        } elseif (in_array(strtolower($dbType), ['blob', 'text'])) {
            $this->setType('text');
        } elseif (preg_match('/varchar\(([0-9]+)\)/', $dbType, $matches)) {
            $this->setType('string');
            $this->setLength($matches[1]);
        } elseif (preg_match('/float\(([0-9]+),([0-9]+)\)/', $dbType, $matches)) {
            $this->setType('float');
            $this->setPrecision($matches[1]);
            $this->setScale($matches[2]);
        } elseif (in_array(strtolower($dbType), ['date', 'datetime'])) {
            $this->setType('datetime');
        } else {
            $this->setType('string');
            $this->setLength(255);
        }

        return $this->getType();
    }

    /**
     * Return converted type for Entity class.
     */
    public function getTypeForClass(): ?string
    {
        $conversion = [
            'integer' => 'int',
            'date' => '\\DateTime',
            'datetime' => '\\DateTime',
            'text' => 'string',
            'blob' => 'string',
            'boolean' => 'bool',
            '\Doctrine\Common\Collections\ArrayCollection' => 'Collection',
            'uuid' => 'UuidInterface',
        ];

        if (isset($conversion[$this->getType()])) {
            return $conversion[$this->getType()];
        }

        return $this->isNativeType() ? $this->getType() : $this->getEntityClassName();
    }

    public function getUseForClass(): ?string
    {
        $conversion = [
            'ArrayCollection' => '\Doctrine\Common\Collections\ArrayCollection',
        ];

        return $conversion[$this->getTypeForClass()] ?? null;
    }

    /**
     * Get the entity class name without namespace.
     */
    public function getEntityClassName(): ?string
    {
        $entityType = $this->getEntityType();

        if (!empty($entityType)) {
            $exploded = explode('\\', $entityType);
            if (count($exploded)) {
                return $exploded[count($exploded) - 1];
            }
        }

        return $entityType;
    }

    public function getEntityNamespace(): ?string
    {
        $entityType = $this->getEntityType();

        if (!empty($entityType)) {
            $array = explode('\\', $entityType);
            if (count($array) > 1) {
                unset($array[count($array) - 1]);

                return implode('\\', $array);
            }
        }

        return null;
    }

    public function isReferential(): bool
    {
        return !$this->isNativeType() && 1 === preg_match('/^ref[A-Z][a-z]+/', $this->getName());
    }
}
