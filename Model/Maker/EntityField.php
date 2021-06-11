<?php

namespace EasyApiBundle\Model\Maker;

use Doctrine\Common\Inflector\Inflector;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;

class EntityField
{
    /**
     * @var EntityConfiguration
     */
    protected $entity;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $tableColumnName;

    /**
     * @var string
     */
    protected $referencedColumnName;

    /**
     * @var string
     */
    protected $inverseTableColumnName;

    /**
     * @var string
     */
    protected $inverseReferencedColumnName;

    /**
     * @var string
     */
    protected $joinTable;

    /**
     * @var string
     */
    protected $joinTableSchema;

    /**
     * @var bool
     */
    protected $isPrimary = false;

    /**
     * @var bool
     */
    protected $isNativeType = true;

    /**
     * @var string
     */
    protected $nativeType;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $entityType;

    /**
     * @var string
     */
    protected $relationType;

    /**
     * @var bool
     */
    protected $isRequired = false;

    /**
     * @var bool
     */
    protected $isAutoIncremented = false;

    /**
     * @var mixed
     */
    protected $default;

    /**
     * @var int
     */
    protected $length;

    /**
     * Precision in case of float type.
     *
     * @var int
     */
    protected $precision;

    /**
     * Scale in case of float type.
     *
     * @var int
     */
    protected $scale;

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

    /**
     * @return EntityConfiguration
     */
    public function getEntity(): EntityConfiguration
    {
        return $this->entity;
    }

    /**
     * @param EntityConfiguration $entity
     */
    public function setEntity(EntityConfiguration $entity): void
    {
        $this->entity = $entity;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
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

    /**
     * @param string $tableColumnName
     */
    public function setTableColumnName(string $tableColumnName): void
    {
        $this->tableColumnName = $tableColumnName;
    }

    /**
     * @return string
     */
    public function getReferencedColumnName(): ?string
    {
        return $this->referencedColumnName;
    }

    /**
     * @param string $referencedColumnName
     */
    public function setReferencedColumnName(string $referencedColumnName): void
    {
        $this->referencedColumnName = $referencedColumnName;
    }

    /**
     * @return string
     */
    public function getInverseTableColumnName(): ?string
    {
        return $this->inverseTableColumnName;
    }

    /**
     * @param string $inverseTableColumnName
     */
    public function setInverseTableColumnName(?string $inverseTableColumnName): void
    {
        $this->inverseTableColumnName = $inverseTableColumnName;
    }

    /**
     * @return string
     */
    public function getInverseReferencedColumnName(): ?string
    {
        return $this->inverseReferencedColumnName;
    }

    /**
     * @param string|null $inverseReferencedColumnName
     */
    public function setInverseReferencedColumnName(?string $inverseReferencedColumnName): void
    {
        $this->inverseReferencedColumnName = $inverseReferencedColumnName;
    }

    /**
     * @return string
     */
    public function getJoinTable(): string
    {
        return $this->joinTable;
    }

    /**
     * @param string $joinTable
     */
    public function setJoinTable(string $joinTable): void
    {
        $this->joinTable = $joinTable;
    }

    /**
     * @return string
     */
    public function getJoinTableSchema(): string
    {
        return $this->joinTableSchema;
    }

    /**
     * @param string $joinTableSchema
     */
    public function setJoinTableSchema(string $joinTableSchema): void
    {
        $this->joinTableSchema = $joinTableSchema;
    }

    /**
     * @return bool
     */
    public function isPrimary(): bool
    {
        return $this->isPrimary;
    }

    /**
     * @param bool $isPrimary
     */
    public function setIsPrimary(bool $isPrimary)
    {
        $this->isPrimary = $isPrimary;
    }

    /**
     * @return bool
     */
    public function isNativeType(): bool
    {
        return $this->isNativeType;
    }

    /**
     * @param bool $isNativeType
     */
    public function setIsNativeType(bool $isNativeType)
    {
        $this->isNativeType = $isNativeType;
    }

    /**
     * @return string
     */
    public function getNativeType(): string
    {
        return $this->nativeType;
    }

    /**
     * @param string $nativeType
     */
    public function setNativeType(string $nativeType): void
    {
        $this->nativeType = $nativeType;
    }

    /**
     * @return string
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getEntityType(): ?string
    {
        return $this->entityType;
    }

    /**
     * @param string $entityType
     */
    public function setEntityType(string $entityType)
    {
        $this->entityType = $entityType;
    }

    /**
     * @return string
     */
    public function getRelationType(): ?string
    {
        return $this->relationType;
    }

    /**
     * @param string $relationType
     */
    public function setRelationType(string $relationType)
    {
        $this->relationType = $relationType;
    }

    /**
     * @return bool
     */
    public function isRequired(): bool
    {
        return $this->isRequired;
    }

    /**
     * @param bool $isRequired
     */
    public function setIsRequired(bool $isRequired)
    {
        $this->isRequired = $isRequired;
    }

    /**
     * @return bool
     */
    public function isAutoIncremented(): bool
    {
        return $this->isAutoIncremented;
    }

    /**
     * @param bool $isAutoIncremented
     */
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

    public function getDefaultValue()
    {
        if ('boolean' === $this->getType()) {
            return (bool) $this->getDefault();
        }

        return $this->getDefault();
    }

    /**
     * @return int
     */
    public function getLength(): ?int
    {
        return $this->length;
    }

    /**
     * @param int $length
     */
    public function setLength(?int $length)
    {
        $this->length = $length;
    }

    /**
     * @return int
     */
    public function getPrecision(): int
    {
        return $this->precision;
    }

    /**
     * @param int $precision
     */
    public function setPrecision(?int $precision)
    {
        $this->precision = $precision;
    }

    /**
     * @return int
     */
    public function getScale(): int
    {
        return $this->scale;
    }

    /**
     * @param int $scale
     */
    public function setScale(?int $scale)
    {
        $this->scale = $scale;
    }

    /**
     * @return string
     */
    public function getGetterName()
    {
        return 'boolean' === $this->getType() ? ('is' === substr($this->name, 0, 2) ? $this->name : 'is'.ucfirst($this->name)) : 'get'.ucfirst($this->name);
    }

    /**
     * @return string
     */
    public function getSetterName()
    {
        return 'set'.ucfirst($this->name);
    }

    /**
     * @return string
     */
    public function getAdderName()
    {
        if ('array' === $this->getType() || '\Doctrine\Common\Collections\ArrayCollection' === $this->getType()) {
            return 'add'.ucfirst(Inflector::singularize($this->getName()));
        }
    }

    /**
     * @return string
     */
    public function getRemoverName()
    {
        if ('array' === $this->getType() || '\Doctrine\Common\Collections\ArrayCollection' === $this->getType()) {
            return 'remove'.ucfirst(Inflector::singularize($this->getName()));
        }
    }

    /**
     * Get type for the serializer.
     *
     * @return string
     */
    public function getSerializerType()
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
     * @param bool $forceNew
     *
     * @return int|mixed|string
     */
    public function getRandomValue($forceNew = false)
    {
        if (null === $this->randomValue || $forceNew) {
            $this->randomValue = $this->generateRandomValue();
        }

        return $this->randomValue;
    }

    /**
     * @param $value
     */
    public function setRandomValue($value)
    {
        $this->randomValue = $value;
    }

    /**
     * @return array|int|UuidInterface
     *
     * @throws \Exception
     */
    protected function generateRandomValue()
    {
        if ($this->isNativeType() && !$this->isPrimary()) {
            if ('date' === strtolower($this->getType())) {
                return '2018-08-03';
            }

            if ('datetime' === strtolower($this->getType())) {
                return '2018-08-03 12:11:10';
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
                return 'string_'.$this->uniqueRandomNumbersWithinRange(0, 99);
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
     *
     * @param $min
     * @param $max
     *
     * @return array
     */
    protected function uniqueRandomNumbersWithinRange($min, $max)
    {
        $numbers = range($min, $max);
        shuffle($numbers);

        return array_slice($numbers, 0, 1)[0];
    }

    /**
     * @param string $dbType
     * @return string
     */
    public function setTypeFromMysqlType(string $dbType): string
    {
        if (in_array(strtolower($dbType), ['tinyint(1)', 'bool', 'boolean'])) {
            $this->setType('boolean');
        } elseif (preg_match('/int\(([0-9]+)\)/', $dbType, $matches)) {
            $this->setType('integer');
        } elseif (preg_match('/varchar\(([0-9]+)\)/', $dbType, $matches)) {
            $this->setType('string');
            $this->setLength($matches[1]);
        } elseif (preg_match('/float\(([0-9]+),([0-9]+)\)/', $dbType, $matches)) {
            $this->setType('float');
            $this->setPrecision($matches[1]);
            $this->setScale($matches[2]);
        } elseif (0 === strpos($dbType, 'date')) {
            $this->setType('\\DateTime');
        } elseif (0 === strpos($dbType, 'datetime')) {
            $this->setType('\\DateTime');
        } elseif (0 === strpos($dbType, 'bool')) {
            $this->setType('\\DateTime');
        } else {
            $this->setType('string');
            $this->setLength(255);
        }

        return $this->getType();
    }

    /**
     * Return converted type for Entity class.
     *
     * @return mixed|string|null
     */
    public function getTypeForClass()
    {
        $conversion = [
            'integer' => 'int',
            'date' => '\\DateTime',
            'datetime' => '\\DateTime',
            'text' => 'string',
            'boolean' => 'bool',
            '\Doctrine\Common\Collections\ArrayCollection' => 'Collection',
            'uuid' => 'UuidInterface',
        ];

        if (isset($conversion[$this->getType()])) {
            return $conversion[$this->getType()];
        }

        return $this->isNativeType() ? $this->getType() : $this->getEntityClassName();
    }

    /**
     * @return mixed|null
     */
    public function getUseForClass()
    {
        $conversion = [
            'ArrayCollection' => '\Doctrine\Common\Collections\ArrayCollection',
        ];

        return $conversion[$this->getTypeForClass()] ?? null;
    }

    /**
     * Get the entity class name without namespace.
     *
     * @return string|null
     */
    public function getEntityClassName()
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

    /**
     * @return string|null
     */
    public function getEntityNamespace()
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

    /**
     * @return bool
     */
    public function isReferential()
    {
        return !$this->isNativeType() && 1 === preg_match('/^ref[A-Z][a-z]+/', $this->getName());
    }
}