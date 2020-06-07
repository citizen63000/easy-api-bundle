<?php


namespace EasyApiBundle\Util\Maker;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\EntityManager;
use EasyApiBundle\Model\Maker\EntityConfiguration;
use EasyApiBundle\Model\Maker\EntityField;
use EasyApiBundle\Util\StringUtils\CaseConverter;

class EntityConfigLoader
{
    /**
     * @var EntityConfiguration
     */
    protected $config;

    /**
     * @var array
     */
    protected static $possibleNativeTypes = ['float', 'string', 'integer', 'DateTime[<>-a-zA-Z]*'];

    /**
     * @param EntityManager $em
     * @param string $bundle
     * @param string $entityName
     * @param string $tableName
     * @param string|null $parentEntityName
     * @param string|null $inheritanceType
     * @param string|null $context
     * @return EntityConfiguration
     * @throws \Doctrine\DBAL\DBALException
     */
    public static function createEntityConfigFromDatabase(EntityManager $em, string $bundle ,string $entityName, string $tableName, string $parentEntityName = null, string $inheritanceType = null, string $context =null)
    {
        $config = new EntityConfiguration();
        $config->setTableName($tableName);
        $config->setEntityName($entityName);

        // namespace
        if(null !== $context) {
            $config->setNamespace("{$bundle}\Entity\\{$context}");
        } else {
            $config->setNamespace("{$bundle}\Entity");
        }

        //parent
        if(!empty($parentEntityName)) {
            $config->setParentEntity(self::findAndCreateFromEntityName($parentEntityName, $bundle));
        }

        // fields
        $dbLoader = new DatabaseConfigurationLoader($em);
        $tableDescription = $dbLoader->load($tableName);
        foreach ($tableDescription['columns'] as $column) {
//            var_dump($column);

            if(preg_match('/([_a-zA-Z0-9]+)_(id|code)$/', $column['Field'], $matches)) {
                $config = static::addManyToOneOrOneToOne($config, $column['Field'], $matches[2], CaseConverter::convertSnakeCaseToPascalCase($matches[1]));
            } else {
                $config = static::addNativeField($config, $column);
            }

            // search oneToMany

            // search ManyToMany

        }

        // discriminatorType
//        if (isset($content[key($content)]['inheritanceType'])) {
//            $config->setIsParentEntity(true);
//            $config->setInheritanceType($content[key($content)]['inheritanceType']);
//        }

//        // oneToMany
//        if (isset($content[key($content)]['oneToMany'])) {
//            $fields = $content[key($content)]['oneToMany'];
//            foreach ($fields as $name => $field) {
//                $newField = new entityField();
//                $newField->setName($name);
//                $newField->setType('\Doctrine\Common\Collections\ArrayCollection');
//                $newField->setRelationType('oneToMany');
//                $newField->setEntityType($field['targetEntity']);
//                $newField->setIsNativeType(false);
//
//                $config->addField($newField);
//            }
//        }
//
//        // manyToMany
//        if (isset($content[key($content)]['manyToMany'])) {
//            $fields = $content[key($content)]['manyToMany'];
//            foreach ($fields as $name => $field) {
//                $newField = new entityField();
//                $newField->setName($name);
//                $newField->setType('\Doctrine\Common\Collections\ArrayCollection');
//                $newField->setRelationType('manyToMany');
//                $newField->setEntityType($field['targetEntity']);
//                $newField->setIsNativeType(false);
//
//                $config->addField($newField);
//            }
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
        $relationType = 'manyToOne';
        $entityConfig = self::findAndCreateFromEntityName($entityName, $bundle);

        if($entityConfig->hasField(lcfirst($config->getEntityName()))) {
            $relationType = 'oneToOne';
        } else {

            // search unique index on this field

        }

        if(null !== $entityConfig) {
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
     * @param array $content
     *
     * @return EntityConfigLoader
     */
    public static function createFromFileContent(array $content)
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
     * @param string $filepath
     * @return EntityConfiguration
     * @throws \Exception
     */
    protected static function createEntityConfigFromAnnotations(string $filepath)
    {
        try {
            $fileContent = file_get_contents($filepath);
            preg_match('/namespace ([a-zA-Z\\\]+);/', $fileContent, $matches);
            $namespace = $matches[1];
            preg_match('/class ([a-zA-Z]+)/', $fileContent, $matches);
            $classname = $matches[1];
        } catch (\Exception $e) {
            throw new \Exception('Impossible to load class '.$filepath.' '.$e->getMessage());
        }

        $conf = new EntityConfiguration();
        $r = new \ReflectionClass("{$namespace}\\{$classname}");

        foreach ($r->getProperties() as $var) {

            $reader = new AnnotationReader();
            $annotations = $reader->getPropertyAnnotations($var);

            $field = new EntityField();
            $field->setName($var->getName());

            foreach ($annotations as $annotation) {

                switch (get_class($annotation)) {
                    case 'Doctrine\ORM\Mapping\Id':
                        $field->isPrimary(true);
                        break;
                    case 'Doctrine\ORM\Mapping\GeneratedValue':
                        $field->isAutoIncremented(true);
                        break;
                    case 'Doctrine\ORM\Mapping\Column':
                        $field->setType($annotation->type);
                        $field->setLength($annotation->length);
                        $field->setPrecision($annotation->precision);
                        $field->setScale($annotation->scale);
//                        $field->setUnique($annotation->unique);
//                        $field->setNullable($annotation->nullable);

                        if(count($annotation->options)) {

                        }
                    case 'Doctrine\ORM\Mapping\ManyToOne':
                //                $newField->setEntityType($field['targetEntity']);
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
                    case 'Doctrine\ORM\Mapping\OneToOne':
                //                $newField->setEntityType($field['targetEntity']);
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
                    case 'Doctrine\ORM\Mapping\OneToMany':
//                      $newField->setType('\Doctrine\Common\Collections\ArrayCollection');
//                      $newField->setEntityType($field['targetEntity']);
                        $field->setRelationType('oneToMany');
                        $field->setIsNativeType(false);
                //
                //                if (isset($field['joinColumns'])) {
                //                    $newField->setTableColumnName(array_keys($field['joinColumns'])[0]);
                //                }
                //
                //            }
                        break;
                    case 'Doctrine\ORM\Mapping\ManyToMany':
//                $newField->setType('\Doctrine\Common\Collections\ArrayCollection');
//                $newField->setEntityType($field['targetEntity']);
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

                $conf->addField($field);
            }
        }

        $conf->setEntityFileClassPath($filepath);
        $conf->setNamespace($r->getNamespaceName());
        $conf->setEntityName($classname);

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
     * @param $groups
     *
     * @return array
     */
    public function getProperties($groups)
    {
        $properties = [];

        if ($this->config) {
            $possibleProperties = $this->config->getProperties();
            foreach ($possibleProperties as $key => $property) {
                if (self::propertyHasValidGroup($property, $groups)) {
                    if (isset($property['type']) && !self::isNativeType($property['type'])) {
                        $serializerReader = new JmsSerializerConfigReader($property['type']);
                        $properties[] = [
                            'name' => $property['serialized_name'] ?? $key,
                            'type' => 'object',
                            'data' => $serializerReader->getProperties($groups),
                        ];
                    } else {
                        $properties[] = [
                            'name' => $property['serialized_name'] ?? $key,
                            'type' => $property['type'] ?? 'string',
                        ];
                    }
                }
            }
        }

        return $properties;
    }

    /**
     * Return the classname by reading the full entity type.
     *
     * @param $fullEntityType
     *
     * @return string
     */
    protected static function getShortEntityType($fullEntityType)
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
            $dir = "src/{$bundle}/Entity/";
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

                if(is_dir("{$dir}{$file}") && '.' !== $file && '..' !== $file) {

                    if($path = self::findFile("{$dir}{$file}", $fileNameExpr)) {
                        return $path;
                    };
                }

                if (preg_match($fileNameExpr, $file)) {
                    return "{$dir}/{$file}";
                }
            }
        }

        return null;
    }
}