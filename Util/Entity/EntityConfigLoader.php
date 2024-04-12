<?php

namespace EasyApiBundle\Util\Entity;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embedded;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\InheritanceType;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\JoinColumns;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\MappedSuperclass;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;
use EasyApiBundle\Model\EntityConfiguration;
use EasyApiBundle\Model\EntityField;
use EasyApiBundle\Util\StringUtils\CaseConverter;
use EasyApiBundle\Util\StringUtils\Inflector;
use Exception;
use ReflectionClass;
use ReflectionProperty;

class EntityConfigLoader
{
    /** @var EntityConfiguration */
    protected $config;

    /** @var string[]  */
    protected static $possibleNativeTypes = ['float', 'string', 'integer', 'int', 'bool', 'DateTime[<>-a-zA-Z]*'];

    public static function createEntityConfigFromDatabase(EntityManager $em, string $entityName, string $tableName, string $schema = null, string $parentEntityName = null, string $inheritanceType = null, string $context =null): EntityConfiguration
    {
        $config = new EntityConfiguration();
        $config->setTableName($tableName);
        $config->setSchema($schema);
        $config->setEntityName($entityName);

        // namespace
        if (null !== $context) {
            $contextNamespace = str_replace('/', '\\', $context);
            $config->setNamespace("App\Entity\\{$contextNamespace}");
        } else {
            $config->setNamespace("App\Entity");
        }

        //parent
        if (!empty($parentEntityName)) {
            $config->setParentEntity(self::findAndCreateFromEntityName($parentEntityName));
        }

        // fields
        $dbLoader = new DatabaseConfigurationLoader($em);
        $tableDescription = $dbLoader->load($tableName, $schema);
        foreach ($tableDescription['columns'] as $column) {
            if (preg_match('/([_a-zA-Z0-9]+)_(id|code)$/', $column['Field'], $matches)) {
                $config = static::addManyToOneOrOneToOne($config, $column, $matches[2], CaseConverter::convertSnakeCaseToPascalCase($matches[1]));
            } else {
                $config = static::addNativeField($config, $column);
            }
        }

        // search oneToMany and  ManyToMany
        foreach ($tableDescription['relations'] as $relation) {
            $config = static::addOneToManyAndManyToMany($config, $relation);
        }

        // discriminatorType
//        if (isset($content[key($content)]['inheritanceType'])) {
//            $config->setIsParentEntity(true);
//            $config->setInheritanceType($content[key($content)]['inheritanceType']);
//        }

        return $config;
    }

    protected static function addNativeField(EntityConfiguration $config, array $column): EntityConfiguration
    {
        $field = new EntityField();
        $field->setTableColumnName($column['Field']);
        $field->setName(CaseConverter::convertSnakeCaseToCamelCase($column['Field']));
        $field->setIsRequired($column['Null'] == 'NO');

        $field->setNativeType($column['Type']);
        $field->setIsNativeType(true);

        $field->setTypeFromMysqlType($field->getNativeType());
        $field->setDefault($column['Default']);

        if ('PRI' === $column['Key']) {
            $field->setIsPrimary(true);
            if (isset($column['Extra']) && 'auto_increment' === $column['Extra']) {
                $field->setIsAutoIncremented(true);
            }
        }

        if ('created_at' === $column['Field']) {
            $config->setIsTimestampable(true);
        }

        $config->addField($field);

        return $config;
    }

    protected static function addManyToOneOrOneToOne(EntityConfiguration $config, array $column, string $referencedColumnName, string $entityName): EntityConfiguration
    {
        $columnName = $column['Field'];
        if ("{$config->getTableName()}_id" === $columnName) {
            // self-referenced entity
            $entityConfig = $config;
        } else {
            $entityConfig = self::findAndCreateFromEntityName($entityName);
        }

        if (null !== $entityConfig) {

            $relationType = 'manyToOne';
            if ($entityConfig->hasField(lcfirst($config->getEntityName()))) {
                $relationType = 'oneToOne';
            } else {
                // @TODO search unique index on this field
            }

            $newField = new entityField();
            $newField->setName(lcfirst($entityName));
            $newField->setEntityType($entityConfig->getFullName());
            $newField->setType($newField->getEntityClassName());
            $newField->setRelationType($relationType);
            $newField->setIsNativeType(false);
            $newField->setIsRequired($column['Null'] == 'NO');
            $newField->setTableColumnName($columnName);
            $newField->setReferencedColumnName($referencedColumnName);

            $config->addField($newField);
        }

        return $config;
    }

    protected static function addOneToManyAndManyToMany(EntityConfiguration $config, array $relation): EntityConfiguration
    {
        $newField = new entityField();

        // ManyToMany
        if(preg_match("/{$config->getTableName()}/", $relation['TABLE_NAME']) && isset($relation['target'])) {
            $relationType = 'manyToMany';
            $newField->setName(Inflector::pluralize(CaseConverter::convertSnakeCaseToCamelCase($relation['target']['REFERENCED_TABLE_NAME'])));
            $newField->setReferencedColumnName($relation['REFERENCED_COLUMN_NAME']);
            $newField->setTableColumnName($relation['COLUMN_NAME']);
            $newField->setJoinTable($relation['TABLE_NAME']);
            $newField->setJoinTableSchema($relation['TABLE_SCHEMA']);

            // load link table
            $newField->setInverseReferencedColumnName($relation['target']['REFERENCED_COLUMN_NAME']);
            $newField->setInverseTableColumnName($relation['target']['COLUMN_NAME']);

            $entityName = CaseConverter::convertSnakeCaseToPascalCase($relation['target']['REFERENCED_TABLE_NAME']);
            $entityConfig = self::findAndCreateFromEntityName($entityName);
            $newField->setEntityType($entityConfig ? $entityConfig->getFullName() : $entityName);

        } else { // oneToMany
            $relationType = 'oneToMany';
            $newField->setName(Inflector::pluralize(CaseConverter::convertSnakeCaseToCamelCase($relation['TABLE_NAME'])));
            $entityName = CaseConverter::convertSnakeCaseToPascalCase($relation['TABLE_NAME']);
            $entityConfig = self::findAndCreateFromEntityName($entityName);
            $newField->setEntityType($entityConfig ? $entityConfig->getFullName() : $entityName);
        }

        $newField->setType('\Doctrine\Common\Collections\ArrayCollection');
        $newField->setRelationType($relationType);
        $newField->setIsNativeType(false);

        $config->addField($newField);

        return $config;
    }

    public static function findAndCreateFromEntityName(string $entityName, string $context = null): ?EntityConfiguration
    {
        return self::findAndCreateFromEntityNameFromAnnotations($entityName, $context);
    }

    protected static function findAndCreateFromEntityNameFromAnnotations(string $entityName, string $context = null): ?EntityConfiguration
    {
        if ($filePath = self::findConfigEntityFileInBundle($entityName, $context)) {
            return self::createEntityConfigFromFileContent($filePath);
        }

        return null;
    }

    protected static function createEntityConfigFromFileContent(string $filePath): ?EntityConfiguration
    {
        return self::createEntityConfigFromAnnotations($filePath);
    }

    public static function createEntityConfigFromAnnotations(?string $filepath, ?string $fullName = null): EntityConfiguration
    {
        if (null !== $filepath) {
            try {
                $fileContent = file_get_contents($filepath);
                preg_match('/namespace ([a-zA-Z0-9\\\]+);/', $fileContent, $matches);
                $namespace = $matches[1];
                preg_match('/class ([a-zA-Z0-9]+)/', $fileContent, $matches);
                $classname = $matches[1];
                $fullName = "{$namespace}\\{$classname}";
            } catch (Exception $e) {
                throw new Exception('Impossible to load class '.$filepath.' '.$e->getMessage());
            }
        }

        return static::createEntityConfigFromEntityFullName($fullName);
    }

    /**
     * @todo finish it by adding all annotations options
     * @throws \ReflectionException
     */
    public static function createEntityConfigFromEntityFullName(string $fullName): EntityConfiguration
    {
        $conf = new EntityConfiguration();
        try {
            $r = new ReflectionClass($fullName);
        } catch (Exception $e) {
            echo 'Impossible de charger la classe'.$fullName;die;
        }

        // class annotations
        $reader = new AnnotationReader();
        $annotations = $reader->getClassAnnotations($r);

        foreach ($annotations as $annotation) {
            switch (get_class($annotation)) {
                case Table::class:
                    $conf->setTableName($annotation->name);
                    if(isset($annotation->schema)) {
                        $conf->setSchema($annotation->schema);
                    }
                    break;
                case MappedSuperclass::class:
                    $conf->setMappedSuperclass(true);
                    break;
                case InheritanceType::class:
                    $conf->setInheritanceType($annotation->value);
                    break;
            }
        }

        // fields annotations
        foreach ($r->getProperties() as $var) {

            $reader = new AnnotationReader();
            $annotations = $reader->getPropertyAnnotations($var);

            $field = new EntityField();
            $field->setName($var->getName());

            foreach ($annotations as $annotation) {

                switch (get_class($annotation)) {
                    case Id::class:
                        $field->setIsPrimary(true);
                        break;
                    case GeneratedValue::class:
                        $field->setIsAutoIncremented(true);
                        break;
                    case Column::class:
                        $field->setType($annotation->type);
                        $field->setLength($annotation->length);
                        $field->setPrecision($annotation->precision);
                        $field->setScale($annotation->scale);
                        $field->setIsRequired(!$annotation->nullable);
                        if (count($annotation->options)) {

                        }
                        break;
                    case JoinColumns::class:
                        if (isset($annotation->value[0]) && JoinColumn::class == get_class($annotation->value[0])) {
                            $field->setIsRequired(!$annotation->value[0]->nullable);
                        }
                        break;
                    case ManyToOne::class:
                        $field->setEntityType($annotation->targetEntity);
                        //                $newField->setType($newField->getEntityClassName());
                        $field->setRelationType('manyToOne');
                        $field->setIsNativeType(false);
                        //
                        //                if (isset($field['joinColumns'])) {
                        //                    $newField->setTableColumnName(array_keys($field['joinColumns'])[0]);
                        //                }
                        //
                        //            }
                        break;
                    case OneToOne::class:
                        $field->setEntityType($annotation->targetEntity);
                        //                $newField->setType($newField->getEntityClassName());
                        $field->setRelationType('oneToOne');
                        $field->setIsNativeType(false);
                        //
                        //                if (isset($field['joinColumns'])) {
                        //                    $newField->setTableColumnName(array_keys($field['joinColumns'])[0]);
                        //                }
                        //
                        //            }
                        break;
                    case OneToMany::class:
                        $field->setType('\Doctrine\Common\Collections\ArrayCollection');
                        $field->setEntityType($annotation->targetEntity);
                        $field->setRelationType('oneToMany');
                        $field->setIsNativeType(false);
                        //
                        //                if (isset($field['joinColumns'])) {
                        //                    $newField->setTableColumnName(array_keys($field['joinColumns'])[0]);
                        //                }
                        //
                        //            }
                        break;
                    case ManyToMany::class:
                        $field->setType('\Doctrine\Common\Collections\ArrayCollection');
                        $field->setEntityType($annotation->targetEntity);
                        $field->setRelationType('manyToMany');
                        $field->setIsNativeType(false);
                        //
                        //                if (isset($field['joinColumns'])) {
                        //                    $newField->setTableColumnName(array_keys($field['joinColumns'])[0]);
                        //                }
                        //
                        //            }
                        break;
                    case Embedded::class:
                        $field->setEntityType($annotation->class);
                        $field->setIsNativeType(false);
                        break;
                }
            }

            if(null === $field->getType()) {
                $rp = new ReflectionProperty($fullName, $var->getName());
                if (preg_match('/@var[ \\\]+([a-zA-Z]+)/', $rp->getDocComment(), $matches)) {
                    foreach (self::$possibleNativeTypes as $nativeType) {
                        if (preg_match("/{$nativeType}/", $matches[1])) {
                            $field->setType($matches[1]);
                        }
                    }
                }
            }

            $conf->addField($field);
        }

        if($parentClass = $r->getParentClass()) {
            $parentConfig = self::createEntityConfigFromEntityFullName($parentClass->getName());
            $conf->setParentEntity($parentConfig);
        }

//        $conf->setEntityFileClassPath($filepath);
        $conf->setEntityFileClassPath($r->getFileName());
        $conf->setNamespace($r->getNamespaceName());
        $conf->setEntityName(EntityConfiguration::getEntityNameFromNamespace($fullName));

        return $conf;
    }

    protected static function findParentConfig(EntityConfiguration $childConfig): ?EntityConfiguration
    {
        $classFile = $childConfig->getNamespace().$childConfig->getEntityName().'.php';

        if (file_exists($classFile)) {
            $content = file_get_contents($classFile);
            if (preg_match('/class [a-zA-Z0-9]+ extends ([a-zA-Z0-9])/', $content, $matches)) {
                return self::findAndCreateFromEntityName($matches[1]);
            }
        }

        return null;
    }

    /**
     * Return the classname by reading the full entity type.
     */
    public static function getShortEntityType($fullEntityType): ?string
    {
        $tab = explode('\\', $fullEntityType);

        return $tab[count($tab) - 1];
    }

    /**
     * Return the namespace by reading the full entity type.
     */
    protected static function getNamespace(string $fullEntityType): string
    {
        return substr($fullEntityType, 0, strlen($fullEntityType) - strlen(self::getShortEntityType($fullEntityType)) - 1);
    }

    protected static function isNativeType(string $type): bool
    {
        foreach (self::$possibleNativeTypes as $nativeType) {
            if ($type === $nativeType || preg_match("/{$nativeType}/", $type)) {
                return true;
            }
        }

        return false;
    }

    public function getConfig(): EntityConfiguration
    {
        return $this->config;
    }

    public static function findConfigEntityFileInBundle(string $entityName, string $context = null): ?string
    {
        $contextpath = str_replace('\\', '/', $context);
        $entityName = self::getShortEntityType($entityName);
        $dir = "src/Entity/{$contextpath}";
        $fileNameExpr = "/{$entityName}.php$/";

        return self::findFile($dir, $fileNameExpr);
    }

    protected static function findFile(string $dir, string $fileNameExpr): ?string
    {
        if (file_exists($dir)) {
            $files = scandir($dir);

            foreach ($files as $file) {

                if (is_dir("{$dir}/{$file}") && '.' !== $file && '..' !== $file) {

                    if ($path = self::findFile("{$dir}/{$file}", $fileNameExpr)) {
                        return $path;
                    }
                }

                if (preg_match($fileNameExpr, $file)) {
                    return '/' === $dir[strlen($dir)-1] ? "{$dir}{$file}" : "{$dir}/{$file}";
                }
            }
        }
        return null;
    }
}
