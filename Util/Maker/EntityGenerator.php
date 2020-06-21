<?php


namespace EasyApiBundle\Util\Maker;

use EasyApiBundle\Model\Maker\EntityField;
use EasyApiBundle\Util\StringUtils\CaseConverter;
use Symfony\Component\HttpFoundation\Response;

class EntityGenerator extends AbstractGenerator
{
    public const DEFAULT_ENTITY_SKELETON = 'doctrine/entity.php.twig';
    protected $useDoctrineAnnotations = true;

    protected const doctrineAnnotationAlias = 'ORM';
    protected static $doctrineAnnotationPrefix = '@'.self::doctrineAnnotationAlias;

    /**
     * @throws \Exception
     */
    protected function generateContent()
    {
        $content = ['fields' => [], 'uses' => [], '__construct' => ['fields' => []], 'classAnnotations' => []];

        $content['namespace'] = $this->getConfig()->getNamespace();
        $content['classname'] = $this->getConfig()->getEntityName();
        $content['extend'] = $this->getConfig()->getEntityType();
        $parentConfig = $this->getConfig()->getParentEntity();
        $content['parent'] = $parentConfig ? $parentConfig->getEntityName() : null;

        if (null !== $parentConfig) {
            $content['uses'][] = $parentConfig->getNamespace().'\\'.$parentConfig->getEntityName();
        } else {
            // Extends AbstractBaseEntity ?
            if ( null === $content['parent']
                && $this->getConfig()->hasField('id', 'integer', true)
                && $this->getConfig()->hasField('createdAt', '\DateTime')
                && $this->getConfig()->hasField('updatedAt', '\DateTime')
            ) {
                $content['uses'][] = 'EasyApiBundle\Entity\AbstractBaseEntity';
                $this->getConfig()->removeField('id');
                $this->getConfig()->removeField('createdAt');
                $this->getConfig()->removeField('updatedAt');
                $content['parent'] = 'AbstractBaseEntity';
            }
        }

        if($this->useDoctrineAnnotations) {
            $content['uses'][] = 'Doctrine\ORM\Mapping as '.self::doctrineAnnotationAlias;
            $content['classAnnotations'][] = static::$doctrineAnnotationPrefix.'\Entity()';
            $schema = $this->config->getSchema() ? "schema=\"{$this->config->getSchema()}\", ": '' ;
            $content['classAnnotations'][] = static::$doctrineAnnotationPrefix."\\Table({$schema}name=\"`{$this->config->getTableName()}`\")";
        }

        $content['uses'][] = 'Symfony\\Component\\Serializer\\Annotation\\Groups';

        foreach ($this->getConfig()->getFields() as $field) {

            $annotations = $this->useDoctrineAnnotations ? $this->getDoctrineAnnotationsForField($field) : [];
            $annotations = array_merge($annotations, $this->generateSerializerAnnotationsForField($field));

            $content['fields'][] = [
                'name' => $field->getName(),
                'type' => $field->getTypeForClass(),
                'getter' => $field->getGetterName(),
                'setter' => $field->getSetterName(),
                'adder' => $field->getAdderName(),
                'remover' => $field->getRemoverName(),
                'entityClassName' => $field->getEntityClassName(),
                'entityVarName' => lcfirst($field->getEntityClassName()),
                'field' => $field,
                'defaultValue' => 'boolean' === $field->getType() ? ($field->getDefaultValue() ? 'true' : 'false') : $field->getDefaultValue(),
                'annotations' => $annotations,
            ];

            // add use on file if it's not the same namespace
            if (!$field->isNativeType()) {
                if ('\Doctrine\Common\Collections\ArrayCollection' === $field->getType()) {
                    $content['__construct']['fields'][] = ['name' => $field->getName(), 'entityType' => 'ArrayCollection'];
                    if (!in_array('\Doctrine\Common\Collections\ArrayCollection', $content['uses'])) {
                        $content['uses'][] = '\Doctrine\Common\Collections\ArrayCollection';
                    }
                }

                if (!in_array($field->getEntityType(), $content['uses']) && $field->getEntityNamespace() !== $content['namespace']) {
                    $content['uses'][] = $field->getEntityType();
                }
            } elseif ('uuid' === $field->getType()) {
                $content['uses'][] = 'Ramsey\Uuid\Uuid';
                $content['uses'][] = 'Ramsey\Uuid\UuidInterface';
                $content['__construct']['fields'][] = ['name' => $field->getName(), 'entityType' => 'uuid'];
            }
        }

        return $content;
    }

    /**
     * @param EntityField $field
     * @return array
     */
    protected function getDoctrineAnnotationsForField(EntityField $field)
    {
        $annotations = [];
        $options = '';

        $options .= $field->isRequired() ? ', nullable=false' : ', nullable=true';

        if('decimal' === $field->getType()) {
            $options = ', scale=' . $field->getScale() . ', precision=' . $field->getPrecision();
        }

        if ($field->isPrimary()) {
            $annotations[] = static::$doctrineAnnotationPrefix . '\Id()';

            if ($field->isAutoIncremented()) {
                $annotations[] = static::$doctrineAnnotationPrefix . '\GeneratedValue()';
            }
        }

        if ($field->isNativeType()) {
            $annotations[] = static::$doctrineAnnotationPrefix . "\Column(type=\"{$field->getType()}\"{$options})";
        } else {
            switch ($field->getRelationType()) {
                case 'manyToOne':
                    $annotations[] = static::$doctrineAnnotationPrefix . "\ManyToOne(targetEntity=\"{$field->getEntityType()}\")";
                    $joinColumn = static::$doctrineAnnotationPrefix . "\JoinColumn(name=\"{$field->getTableColumnName()}\", referencedColumnName=\"{$field->getReferencedColumnName()}\")";
                    $annotations[] = static::$doctrineAnnotationPrefix . "\JoinColumns({$joinColumn})";
                    break;
                case 'oneToOne':
                    $annotations[] = static::$doctrineAnnotationPrefix . "\OneToOne(targetEntity=\"{$field->getEntityType()}\")";
                    $joinColumn = static::$doctrineAnnotationPrefix . "\JoinColumn(name=\"{$field->getTableColumnName()}\", referencedColumnName=\"{$field->getReferencedColumnName()}\")";
                    $annotations[] = static::$doctrineAnnotationPrefix . "\JoinColumns({$joinColumn})";
                    break;
            }
        }

        return $annotations;
    }

    /**
     * @param EntityField $field
     * @return array
     */
    protected function generateSerializerAnnotationsForField(EntityField $field)
    {
        $prefix = CaseConverter::convertCamelCaseToSnakeCase($this->config->getEntityName());
        $groups = ["{$prefix}_full"];
        if($field->isPrimary()) {
            $groups[] = "{$prefix}_id";
        }

        return ['@Groups({"'.implode('","', $groups).'"})'];
    }

    /**
     * Return the path to the entity skeleton
     *
     * @return string
     */
    protected function getEntitySkeletonPath(): string
    {
        return $this->getTemplatePath(self::DEFAULT_ENTITY_SKELETON);
    }

    /**
     * @param string $bundle
     * @param string $tableName
     * @param string $entityName
     * @param string|null $schema
     * @param string|null $parentName
     * @param string|null $inheritanceType
     * @param string|null $context
     * @param bool $dumpExistingFiles
     * @return string
     * @throws \Doctrine\DBAL\DBALException
     */
    public function generate(string $bundle, string $tableName, string $entityName, string $schema = null, string $parentName = null, string $inheritanceType = null, string $context = null, bool $dumpExistingFiles = true)
    {
        $this->config = $this->loadEntityConfigFromDatabase($bundle, $entityName, $tableName, $schema, $parentName, $inheritanceType, $context);

        $destinationDir = str_replace('\\', '/', 'src\\'.$this->config->getNamespace().'\\');
        $filename = $this->config->getEntityName().'.php';

        $fileContent = $this->getContainer()->get('templating')->render(
            $this->getEntitySkeletonPath(),
            $this->generateContent()
        );

        return $this->writeFile($destinationDir, $filename, $fileContent, $dumpExistingFiles);
    }
}