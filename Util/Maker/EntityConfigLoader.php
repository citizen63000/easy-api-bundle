<?php

namespace EasyApiBundle\Util\Maker;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\InheritanceType;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\MappedSuperclass;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;
use EasyApiBundle\Model\Maker\EntityConfiguration;
use EasyApiBundle\Model\Maker\EntityField;
use EasyApiBundle\Util\StringUtils\CaseConverter;
use EasyApiBundle\Util\StringUtils\Inflector;

class EntityConfigLoader
{
    /**
     * @var EntityConfiguration
     */
    protected $config;

    /**
     * @var array
     */
    protected static $possibleNativeTypes = ['float', 'string', 'integer', 'int', 'bool', 'DateTime[<>-a-zA-Z]*'];

    /**
     * @param EntityManager $em
     * @param string $bundle
     * @param string $entityName
     * @param string $tableName
     * @param string|null $schema
     * @param string|null $parentEntityName
     * @param string|null $inheritanceType
     * @param string|null $context
     * @return EntityConfiguration
     * @throws DBALException
     */
    public static function createEntityConfigFromDatabase(EntityManager $em, string $bundle ,string $entityName, string $tableName, string $schema = null, string $parentEntityName = null, string $inheritanceType = null, string $context =null)
    {
        $config = new EntityConfiguration();
        $config->setTableName($tableName);
        $config->setSchema($schema);
        $config->setEntityName($entityName);

        // namespace
        if(null !== $context) {
            $contextNamespace = str_replace('/', '\\', $context);
            $config->setNamespace("{$bundle}\Entity\\{$contextNamespace}");
        } else {
            $config->setNamespace("{$bundle}\Entity");
        }

        //parent
        if(!empty($parentEntityName)) {
            $config->setParentEntity(self::findAndCreateFromEntityName($parentEntityName, $bundle));
        }

        // fields
        $dbLoader = new DatabaseConfigurationLoader($em);
        $tableDescription = $dbLoader->load($tableName, $schema);
        foreach ($tableDescription['columns'] as $column) {
            if(preg_match('/([_a-zA-Z0-9]+)_(id|code)$/', $column['Field'], $matches)) {
                $config = static::addManyToOneOrOneToOne($config, $column['Field'], $matches[2], CaseConverter::convertSnakeCaseToPascalCase($matches[1]));
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

    /**
     * @param EntityConfiguration $config
     * @param array $column
     * @return EntityConfiguration
     */
    protected static function addNativeField(EntityConfiguration $config, array $column)
    {
        $field = new EntityField();
        $field->setTableColumnName($column['Field']);
        $field->setName(CaseConverter::convertSnakeCaseToCamelCase($column['Field']));
        $field->setIsRequired(true);

        $field->setNativeType($column['Type']);
        $field->setIsNativeType(true);

        $field->setTypeFromMysqlType($field->getNativeType());
        $field->setDefault($column['Default']);

        if('PRI' === $column['Key']) {
            $field->setIsPrimary(true);
            if(isset($column['Extra']) && 'auto_increment' === $column['Extra']) {
                $field->setIsAutoIncremented(true);
            }
        }

        if('created_at' === $column['Field']) {
            $config->setIsTimestampable(true);
        }

        $config->addField($field);

        return $config;
    }

    /**
     * @param EntityConfiguration $config
     * @param string $columnName
     * @param string $referencedColumnName
     * @param string $entityName
     * @param string|null $bundle
     * @return EntityConfiguration
     */
    protected static function addManyToOneOrOneToOne(EntityConfiguration $config, string $columnName, string $referencedColumnName, string $entityName, string $bundle = null)
    {
        if("{$config->getTableName()}_id" === $columnName) {
            // self-referenced entity
            $entityConfig = $config;
        } else {
            $entityConfig = self::findAndCreateFromEntityName($entityName, $bundle);
        }

        if(null !== $entityConfig) {

            $relationType = 'manyToOne';
            if($entityConfig->hasField(lcfirst($config->getEntityName()))) {
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
            $newField->setTableColumnName($columnName);
            $newField->setReferencedColumnName($referencedColumnName);

            $config->addField($newField);
        }

        return $config;
    }

    /**
     * @param EntityConfiguration $config
     * @param array $relation
     * @return EntityConfiguration
     */
    protected static function addOneToManyAndManyToMany(EntityConfiguration $config, array $relation)
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
            $entityConfig = self::findAndCreateFromEntityName($entityName, $config->getBundleName());
            $newField->setEntityType($entityConfig ? $entityConfig->getFullName() : $entityName);

        } else { // oneToMany
            $relationType = 'oneToMany';
            $newField->setName(Inflector::pluralize(CaseConverter::convertSnakeCaseToCamelCase($relation['TABLE_NAME'])));
            $entityName = CaseConverter::convertSnakeCaseToPascalCase($relation['TABLE_NAME']);
            $entityConfig = self::findAndCreateFromEntityName($entityName, $config->getBundleName());
            $newField->setEntityType($entityConfig ? $entityConfig->getFullName() : $entityName);
        }

        $newField->setType('\Doctrine\Common\Collections\ArrayCollection');
        $newField->setRelationType($relationType);
        $newField->setIsNativeType(false);

        $config->addField($newField);

        return $config;
    }

    /**
     * @param array $content
     *
     * @return EntityConfigLoader
     */
    public static function createFromFileContent(array $content): EntityConfigLoader
    {
        reset($content);
        $reader = new self();
        $reader->setEntityType(key($content));

        return $reader;
    }

    /**
     * @param $entityName
     * @param null $defaultBundle
     * @param string $type
     * @return EntityConfiguration|null
     */
    public static function findAndCreateFromEntityName($entityName, $defaultBundle = null, $type = 'annotations')
    {
        switch ($type) {
            case 'annotations':
                return self::findAndCreateFromEntityNameFromAnnotations($entityName, $defaultBundle);
            default:
                return null;
        }
    }

    /**
     * @param $entityName
     * @param $defaultBundle
     *
     * @return EntityConfiguration|null
     */
    protected static function findAndCreateFromEntityNameFromAnnotations($entityName, $defaultBundle = null)
    {
        // Search in the current bundle first
        if (null !== $defaultBundle && $filePath = self::findConfigEntityFileInBundle($entityName, $defaultBundle)) {
            return self::createEntityConfigFromFileContent($filePath);
        }

        // if not found, search in other bundles
        $entityName = self::getShortEntityType($entityName);
        $bundles = AbstractGenerator::getProjectBundles();
        foreach ($bundles as $bundle) {
            if ($bundle !== $defaultBundle && '.' !== $bundle && '..' !== $bundle) {
                if ($filePath = self::findConfigEntityFileInBundle($entityName, $bundle)) {
                    return self::createEntityConfigFromFileContent($filePath);
                }
            }
        }

        return null;
    }

    /**
     * @param string $filePath
     * @param string $type
     * @return void|null
     */
    protected static function createEntityConfigFromFileContent(string $filePath, $type = 'annotations')
    {
        switch ($type) {
            case 'annotations':
                return self::createEntityConfigFromAnnotations($filePath);
            default:
                return null;

        }
    }

    /**
     * @param string|null $filepath
     * @param string|null $fullName
     * @return EntityConfiguration
     * @throws \ReflectionException
     */
    public static function createEntityConfigFromAnnotations(?string $filepath, ?string $fullName = null)
    {
        if(null !== $filepath) {
            try {
                $fileContent = file_get_contents($filepath);
                preg_match('/namespace ([a-zA-Z\\\]+);/', $fileContent, $matches);
                $namespace = $matches[1];
                preg_match('/class ([a-zA-Z]+)/', $fileContent, $matches);
                $classname = $matches[1];
                $fullName = "{$namespace}\\{$classname}";
            } catch (\Exception $e) {
                throw new \Exception('Impossible to load class '.$filepath.' '.$e->getMessage());
            }
        }

        return static::createEntityConfigFromEntityFullName($fullName);
    }

    /**
     * @param string $fullName
     * @return EntityConfiguration
     * @throws \ReflectionException
     */
    public static function createEntityConfigFromEntityFullName(string $fullName)
    {
        $conf = new EntityConfiguration();
        try {
            $r = new \ReflectionClass($fullName);
        } catch (\Exception $e) {
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
                        $field->isAutoIncremented(true);
                        break;
                    case Column::class:
                        $field->setType($annotation->type);
                        $field->setLength($annotation->length);
                        $field->setPrecision($annotation->precision);
                        $field->setScale($annotation->scale);
//                        $field->setUnique($annotation->unique);
//                        $field->setNullable($annotation->nullable);

                        if(count($annotation->options)) {

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
                }
            }

            if(null === $field->getType()) {
                $rp = new \ReflectionProperty($fullName, $var->getName());
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

    /**
     * @param EntityConfiguration $childConfig
     *
     * @return EntityConfiguration|null
     */
    protected static function findParentConfig(EntityConfiguration $childConfig)
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
     *
     * @param $fullEntityType
     *
     * @return string
     */
    public static function getShortEntityType($fullEntityType)
    {
        $tab = explode('\\', $fullEntityType);

        return $tab[count($tab) - 1];
    }

    /**
     * Return the namespace by reading the full entity type.
     *
     * @param $fullEntityType
     *
     * @return string
     */
    protected static function getNamespace($fullEntityType)
    {
        return substr($fullEntityType, 0, strlen($fullEntityType) - strlen(self::getShortEntityType($fullEntityType)) - 1);
    }

    /**
     * @param $type
     *
     * @return bool
     */
    protected static function isNativeType($type)
    {
        foreach (self::$possibleNativeTypes as $nativeType) {
            if ($type === $nativeType || preg_match("/{$nativeType}/", $type)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return EntityConfiguration
     */
    public function getConfig(): EntityConfiguration
    {
        return $this->config;
    }

    /**
     * @param $bundle
     * @param $entityName
     * @param string $type
     *
     * @return string|null
     */
    public function findConfigEntityFileInBundle($entityName, $bundle, $type = 'annotations')
    {
        $entityFileClass = 'src/'.str_replace('\\', '/', $entityName).'.php';
        if('annotation' === $type && file_exists($entityFileClass)) {
            return $entityFileClass;
        }

        $entityName = self::getShortEntityType($entityName);
        if('annotations' === $type) {
            $dir = "src/{$bundle}/Entity";
            $fileNameExpr = "/{$entityName}.php$/";
        } else {
            $dir = "src/{$bundle}/Resources/config/doctrine/";
            $fileNameExpr = "/[\.]{1}{$entityName}.orm.yml$/";
        }

        return self::findFile($dir, $fileNameExpr);
    }

    /**
     * @param $dir
     * @param $fileNameExpr
     * @return string|null
     */
    protected static function findFile($dir, $fileNameExpr)
    {
        if (file_exists($dir)) {
            $files = scandir($dir);

            foreach ($files as $file) {

                if (is_dir("{$dir}/{$file}") && '.' !== $file && '..' !== $file) {

                    if($path = self::findFile("{$dir}/{$file}", $fileNameExpr)) {
                        return $path;
                    };
                }

                if (preg_match($fileNameExpr, $file)) {
                    return '/' === $dir[strlen($dir)-1] ? "{$dir}{$file}" : "{$dir}/{$file}";
                }
            }
        }

        return null;
    }
}
