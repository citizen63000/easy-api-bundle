<?php


namespace EasyApiBundle\Model\Maker;


class EntityConfiguration
{
    public const inheritanceTypeColumnName = 'discriminator_type';

    /**
     * @var string
     */
    protected $entityType;

    /**
     * @var string
     */
    protected $schema;

    /**
     * @var string
     */
    protected $tableName;

    /**
     * @var string
     */
    protected $entityName;

    /**
     * @var string
     */
    protected $entityFileClassPath;

    /**
     * @var string
     */
    protected $namespace;

    /**
     * @var array
     */
    protected $properties;

    /**
     * @var EntityConfiguration
     */
    protected $parentEntity;

    /**
     * @var bool
     */
    protected $isParentEntity = false;

    /**
     * @var string
     */
    protected $repositoryClass;

    /**
     * @var
     */
    protected $inheritanceType;

    /**
     * @var bool
     */
    protected $isTimestampable;

    /**
     * @var array
     */
    protected $fields = [];

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
     * @param EntityField $field
     */
    public function addField(EntityField $field)
    {
        $this->fields[] = $field;
    }

    /**
     * @return string
     */
    public function getSchema(): ?string
    {
        return $this->schema;
    }

    /**
     * @param string $schema
     */
    public function setSchema(?string $schema): void
    {
        $this->schema = $schema;
    }

    /**
     * @return mixed
     */
    public function getTableName(): ?string
    {
        return $this->tableName;
    }

    /**
     * @param mixed $tableName
     */
    public function setTableName(?string $tableName): void
    {
        $this->tableName = $tableName;
    }

    /**
     * @return mixed
     */
    public function getEntityName(): string
    {
        return $this->entityName;
    }

    /**
     * @param mixed $entityName
     */
    public function setEntityName(string $entityName)
    {
        $this->entityName = $entityName;
    }

    /**
     * @return string
     */
    public function getEntityFileClassPath(): string
    {
        return $this->entityFileClassPath;
    }

    /**
     * @return string|null
     */
    public function getEntityFileClassName(): ?string
    {
        if($name = $this->getEntityName()) {

            return "{$name}.php";
        }

        return null;
    }

    /**
     * @return string
     */
    public function getEntityFileClassDirectory(): string
    {
        return str_replace($this->getEntityFileClassName(), '', $this->getEntityFileClassPath());
    }

    /**
     * @param string $entityFileClassPath
     */
    public function setEntityFileClassPath(string $entityFileClassPath): void
    {
        $this->entityFileClassPath = $entityFileClassPath;
    }

    /**
     * @return EntityField[]
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @param array $fields
     */
    public function setFields(array $fields)
    {
        $this->fields = $fields;
    }

    /**
     * @return bool
     */
    public function isTimestampable(): ?bool
    {
        return $this->isTimestampable;
    }

    /**
     * @param bool $isTimestampable
     */
    public function setIsTimestampable(bool $isTimestampable = null): void
    {
        $this->isTimestampable = $isTimestampable;
    }

    /**
     * @return string
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }

    /**
     * @param string $namespace
     */
    public function setNamespace(string $namespace)
    {
        $this->namespace = $namespace;
    }

    /**
     * @return EntityConfiguration
     */
    public function getParentEntity()
    {
        return $this->parentEntity;
    }

    /**
     * @param EntityConfiguration $parentEntity
     */
    public function setParentEntity(EntityConfiguration $parentEntity = null)
    {
        $this->parentEntity = $parentEntity;
    }

    /**
     * @return bool
     */
    public function isParentEntity(): bool
    {
        return $this->isParentEntity;
    }

    /**
     * @param bool $isParentEntity
     */
    public function setIsParentEntity(bool $isParentEntity): void
    {
        $this->isParentEntity = $isParentEntity;
    }

    /**
     * @return string
     */
    public function getRepositoryClass(): string
    {
        return $this->repositoryClass;
    }

    /**
     * @param string $repositoryClass
     */
    public function setRepositoryClass(string $repositoryClass): void
    {
        $this->repositoryClass = $repositoryClass;
    }

    /**
     * Return the name of the bundle.
     *
     * @return string
     */
    public function getBundleName()
    {
        $tab = explode('\\', $this->getNamespace());

        return $tab[0];
    }

    /**
     * Return the name of the context if a context is used.
     *
     * @return string|null
     */
    public function getContextName()
    {
        $tab = explode('\\', $this->getNamespace());
        unset($tab[0], $tab[1]);
        $context = implode('\\', $tab);

        return '' !== $context ? $context : null;
    }

    /**
     * @return mixed
     */
    public function getContextNameForPath()
    {
        return str_replace('\\', '/', $this->getContextName());
    }

    /**
     * Return only idFields.
     *
     * @return array
     */
    public function getIdFields()
    {
        $fields = [];

        foreach ($this->fields as $field) {
            if ($field->isPrimary()) {
                $fields[] = $field;
            }
        }

        return $fields;
    }

    /**
     * @return array
     */
    public function getNativeFields()
    {
        $fields = [];

        foreach ($this->fields as $field) {
            if ($field->isNativeType()) {
                $fields[] = $field;
            }
        }

        return $fields;
    }

    /**
     * @return array
     */
    public function getEntityFields()
    {
        $fields = [];

        foreach ($this->fields as $field) {
            if (!$field->isNativeType()) {
                $fields[] = $field;
            }
        }

        return $fields;
    }

    /**
     * @return array
     */
    public function getRequiredFields()
    {
        $fields = [];

        foreach ($this->fields as $field) {
            if ($field->isRequired()) {
                $fields[] = $field;
            }
        }

        return $fields;
    }

    /**
     * @return mixed
     */
    public function getInheritanceType()
    {
        return $this->inheritanceType;
    }

    /**
     * @param mixed $inheritanceType
     */
    public function setInheritanceType($inheritanceType): void
    {
        $this->inheritanceType = $inheritanceType;
    }

    /**
     * @return bool
     */
    public function isReferential()
    {
        return 1 === preg_match('/Ref[A-Z]{1}[a-z]+/', $this->getEntityName());
    }

    /**
     * @return string
     */
    public function getFullName()
    {
        return "{$this->getNamespace()}\\{$this->getEntityName()}";
    }

    /**
     * @return bool
     */
    public function hasUuid()
    {
        foreach ($this->fields as $field) {
            if('uuid' === $field->getType()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $fieldName
     * @param string|null $fieldType
     * @param bool|null $isPrimary
     * @return bool
     */
    public function hasField(string $fieldName, string $fieldType = null, bool $isPrimary = null): bool
    {
        foreach ($this->getFields() as $field) {
            $isFound = $fieldName === $field->getName();
            $isFound =  null !== $fieldType ? ($fieldType === $field->getType()) : $isFound;
            $isFound =  null !== $isPrimary ? ($isPrimary === $field->isPrimary()) : $isFound;

            if($isFound) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $fieldName
     * @return EntityField|null
     */
    public function getField(string $fieldName): ?EntityField
    {
        foreach ($this->getFields() as $field) {
            if($fieldName === $field->getName()) {
                return $field;
            }
        }

        return null;
    }

    /**
     * @param string $fieldName
     * @return $this
     * @throws \Exception
     */
    public function removeField(string $fieldName)
    {
        foreach ($this->getFields() as $key => $field) {
            if($fieldName === $field->getName()) {
                unset($this->fields[$key]);
                return $this;
            }
        }
        throw new \Exception("Unknow fieldname {$fieldName} to remove in configuration");
    }

    /**
     * @param string $namespace
     * @return string
     */
    public static function getEntityNameFromNamespace(string $namespace)
    {
        $tab = explode('\\', $namespace);

        return $tab[count($tab)-1];
    }
}