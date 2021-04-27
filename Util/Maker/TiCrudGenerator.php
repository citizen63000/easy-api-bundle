<?php

namespace EasyApiBundle\Util\Maker;

use EasyApiBundle\Model\Maker\EntityConfiguration;
use EasyApiBundle\Util\Forms\FormSerializer;
use EasyApiBundle\Util\StringUtils\CaseConverter;
use Symfony\Component\Form\FormInterface;

class TiCrudGenerator extends AbstractGenerator
{
    /**
     * @var string
     */
    protected const DATA_CSV_PATH = 'tests/data/csv/';

    /**
     * @var string
     */
    protected static $templatesDirectory = '/TI/';

    /**
     * @var array
     */
    private static $content;

    /**
     * @var array
     */
    private static $sqlContent;

    /**
     * @param string|null $bundle
     * @param string|null $context
     * @param string $entityName
     * @param bool $dumpExistingFiles boolean
     *
     * @return array paths to the generated files
     * @throws \Exception
     */
    public function generate(?string $bundle, ?string $context, string $entityName, bool $dumpExistingFiles = false): array
    {
        $this->config = $this->loadEntityConfig($entityName, $bundle, $context);

        if(null === $this->config) {
            throw new \Exception("Cannot find entity {$entityName}");
        }

        $paths = [];
        $paths['context'] = $this->generateAbstractContext($dumpExistingFiles);
        $paths['get'] = $this->generateGetTests($dumpExistingFiles);
        $paths['getlist'] = $this->generateGetListTests($dumpExistingFiles);
        $paths['delete'] = $this->generateDeleteTests($dumpExistingFiles);
        $paths['post'] = $this->generatePostTests($dumpExistingFiles);
        $paths['put'] = $this->generatePutTests($dumpExistingFiles);
        $paths['describeform'] = $this->generateDescribeFormTests($dumpExistingFiles);
        $paths['csv'] = $this->generateCsv(static::$content['fixtures'], $dumpExistingFiles);

        return $paths;
    }

    /**
     * Generate Get service tests.
     *
     * @param $dumpExistingFiles boolean
     *
     * @return string
     */
    protected function generateGetTests($dumpExistingFiles = false): string
    {
        $fileContent = $this->getContainer()->get('templating')->render(
            $this->getTemplatePath('crud_get.php.twig'),
            $this->generateContent()
        );

        return $this->writeFile($this->getEntityTestsDirectory(), 'GetTest.php', $fileContent, $dumpExistingFiles, true);
    }

    /**
     * Generate GetList service tests.
     *
     * @param $dumpExistingFiles boolean
     *
     * @return string
     */
    protected function generateGetListTests($dumpExistingFiles = false): string
    {
        $fileContent = $this->getContainer()->get('templating')->render(
            $this->getTemplatePath('crud_get_list.php.twig'),
            $this->generateContent()
        );

        return $this->writeFile($this->getEntityTestsDirectory(), 'GetListTest.php', $fileContent, $dumpExistingFiles, true);
    }

    /**
     * Generate DescribeForm service tests.
     *
     * @param $dumpExistingFiles boolean
     *
     * @return string
     */
    protected function generateDescribeFormTests($dumpExistingFiles = false): string
    {
        $fileContent = $this->getContainer()->get('templating')->render(
            $this->getTemplatePath('crud_describe_form.php.twig'),
            $this->generateContent()
        );

        return $this->writeFile($this->getEntityTestsDirectory(), 'GetDescribeFormTest.php', $fileContent, $dumpExistingFiles, true);
    }

    /**
     * Generate Delete service tests.
     *
     * @param $dumpExistingFiles boolean
     *
     * @return string
     */
    protected function generateDeleteTests($dumpExistingFiles = false): string
    {
        $fileContent = $this->getContainer()->get('templating')->render(
            $this->getTemplatePath('crud_delete.php.twig'),
            $this->generateContent()
        );

        return $this->writeFile($this->getEntityTestsDirectory(), 'DeleteTest.php', $fileContent, $dumpExistingFiles, true);
    }

    /**
     * Generate Post service tests.
     *
     * @param $dumpExistingFiles boolean
     *
     * @return string
     */
    protected function generatePostTests($dumpExistingFiles = false): string
    {
        $fileContent = $this->getContainer()->get('templating')->render(
            $this->getTemplatePath('crud_post.php.twig'),
            $this->generateContent()
        );

        return $this->writeFile($this->getEntityTestsDirectory(), 'PostTest.php', $fileContent, $dumpExistingFiles, true);
    }

    /**
     * Generate Put service tests.
     *
     * @param $dumpExistingFiles boolean
     *
     * @return string
     */
    protected function generatePutTests($dumpExistingFiles = false): string
    {
        $fileContent = $this->getContainer()->get('templating')->render(
            $this->getTemplatePath('crud_put.php.twig'),
            $this->generateContent()
        );

        return $this->writeFile($this->getEntityTestsDirectory(), 'PutTest.php', $fileContent, $dumpExistingFiles, true);
    }

    /**
     * @param $dumpExistingFiles boolean
     *
     * @return string
     */
    protected function generateAbstractContext($dumpExistingFiles = false): string
    {
        $abstractContextName = $this->getAbstractContextTestName();
        $fileContent = $this->getContainer()->get('templating')->render(
            $this->getTemplatePath('abstract_context.php.twig'),
            $this->generateContent()
        );

        return $this->writeFile($this->getEntityTestsDirectory(), "{$abstractContextName}.php", $fileContent, $dumpExistingFiles, true);
    }

    /**
     * @return string
     */
    public function getAbstractContextTestName(): string
    {
        $contextName = str_replace(['\\', '/'], '', $this->config->getContextName());

        return "Abstract{$contextName}{$this->config->getEntityName()}Test";
    }

    /**
     * @param array $fixtures
     * @param bool $dumpExistingFiles
     * @return string
     */
    protected function generateCsv(array $fixtures, bool $dumpExistingFiles = false): string
    {
        $dataFixtures = $this->generateSqlContent($fixtures);

        // yml file
        $ymlWriteOperation = $this->generateDataYml($dataFixtures, $dumpExistingFiles);

        // csv files
        $csvWriteOperation = '';
        $directory = $this->generateDataCsvDirectoryPath();
        foreach ($dataFixtures['tables'] as $table) {

            $fileContent = $this->getContainer()->get('templating')->render(
                $this->getTemplatePath('crud_data.csv.twig'),
                [ 'table' => $table]
            );

            $fileName = str_replace('`', '', "{$table['tableName']}.csv");
            $csvWriteOperation .= 'file://'.$this->writeFile($directory, $fileName, $fileContent, $dumpExistingFiles, true)."\n";
        }

        return "{$ymlWriteOperation}\n{$csvWriteOperation}";
    }

    /**
     * @param array $dataFixtures
     * @param bool $dumpExistingFiles
     * @return string
     */
    protected function generateDataYml(array $dataFixtures, bool $dumpExistingFiles = false): string
    {
        $ymlData = [];
        $path = str_replace(self::DATA_CSV_PATH, '', $this->generateDataCsvDirectoryPath());

        foreach ($dataFixtures['tables'] as $table) {
            $filePath = str_replace('`', '', "{$path}{$table['tableName']}.csv");
            $ymlData[] = [ 'filePath' => $filePath, 'tableName' => "{$table['schema']}.{$table['tableName']}"];
        }

        // yml content
        $fileContent = $this->getContainer()->get('templating')->render(
            $this->getTemplatePath('crud_data.yml.twig'),
            ['files' => $ymlData]
        );

        return $this->writeFile($this->generateDataYmlDirectoryPath(), $this->generateDataYmlFilename(), $fileContent, $dumpExistingFiles, true);
    }

    /**
     * @return string
     */
    protected function generateDataYmlFilename(): string
    {
        return "{$this->config->getEntityName()}.yml";
    }

    /**
     * Return parameters for the template.
     *
     * @return array
     */
    protected function generateContent(): array
    {
        if (empty(static::$content)) {

            // fixtures
            $fixtures =  $this->generateFixtures($this->config);

            // form
            $requiredFieldsForArray = [];
            try {
                $describer = new FormSerializer($this->container->get('form.factory'), $this->container->get('router'), $this->getDoctrine());
                $formName = $this->config->getBundleName().'\\Form\\Type\\'.$this->config->getContextName().'\\'.$this->config->getEntityName().'Type';
                $form = $describer->normalize($this->createForm($formName));

                // requiredFieldsListing
                foreach ($form->getFields() as $field) {
                    if ($field->isRequired()) {
                        $requiredFieldsForArray[] = "'{$field->getName()}'";
                    }
                }
            } catch (\Exception $e) {
                echo "Notice: Maker cannot generate requiredFields because {$e->getMessage()}\n";
            }

            $parentConfig = $this->config->getParentEntity();
            if (null !== $parentConfig) {
                $entityParentUse = $parentConfig->getNamespace().'\\'.$parentConfig->getEntityName();
            } else {
                $entityParentUse = '';
            }

            // content
            static::$content = [
                'entity_name' => $this->config->getEntityName(),
                'entity_use' => $this->config->getNamespace().'\\'.$this->config->getEntityName(),
                'entity_parent_name' => $this->config->getParentEntity() ? $this->config->getParentEntity()->getEntityName() : null,
                'entity_parent_use' => $entityParentUse,
                'bundle_name' => $this->config->getBundleName(),
                'context_name' => $this->config->getContextName(),
                'namespace' => "Tests\\{$this->config->getBundleName()}\\{$this->config->getContextName()}\\{$this->config->getEntityName()}",
                'route_name_prefix' => $this->getRouteNamePrefix().'_'.CaseConverter::convertToPascalCase($this->config->getEntityName()),
                'fields' => $this->config->getFields(),
                'requiredFieldsForArray' => implode(', ', $requiredFieldsForArray),
                'fixtures' => $fixtures,
                'config' => $this->config,
                'additionalInitFile' =>  $this->generateDataSubDirectoryPath().$this->generateDataYmlFilename(),
                'abstractContextName' => $this->getAbstractContextTestName(),
            ];
        }

        return static::$content;
    }

    /**
     * @param array $fixtures
     *
     * @return array
     */
    protected function generateSqlContent(array $fixtures): array
    {
        if (empty(static::$sqlContent)) {
            static::$sqlContent = [
                'tables' => $this->prepareSqlInsertData($this->config, $fixtures),
            ];
        }

        return static::$sqlContent;
    }

    /**
     * @param EntityConfiguration $config
     * @param array $fixtures
     * @param EntityConfiguration|null $childEntityConfig
     * @return array
     */
    protected function prepareSqlInsertData(EntityConfiguration $config, array $fixtures, EntityConfiguration $childEntityConfig = null): array
    {
        $columns = [];
        $values = [];
        $fields = $config->getFields();
        $tables = [];
        $sqlParent = null;

        // Has parent class ?
        if (($parent = $config->getParentEntity()) && !$config->getParentEntity()->isMappedSuperClass()) {
//            $parentConfig = EntityConfigLoader::findAndCreateFromEntityName($parent->getEntityName(), $config->getBundleName());
//            if(null !== $parentConfig) {
//                $sqlParent = $this->prepareSqlInsertData($parentConfig, $fixtures, $config);
//                $columnIdName = $config->isReferential() ? 'code' : 'id';
//                $columns[] = $columnIdName;
//                $values['id'] = $fixtures[$parentConfig->getEntityName()][$columnIdName]['value'];
//            } else {
//                echo "Impossible to find {$parent->getEntityName()} parent class\n";
//            }
        }

        // is parent class ?
        // Inheritance Type column
        if ($config->isParentEntity() && null !== $childEntityConfig) {
            $columns[] = '`'.EntityConfiguration::inheritanceTypeColumnName.'`';
            $values[EntityConfiguration::inheritanceTypeColumnName] = '\''.$childEntityConfig->getEntityName().'\'';
        }

        // Other fields
        foreach ($fields as $field) {
            $columns[] = $field->getTableColumnName();
            if (!$field->isNativeType()) {
                // Referential ?
                if ($field->isReferential()) {
                    try {
                        $values[$field->getTableColumnName()] = $fixtures[$config->getEntityName()][$field->getName()]['value']['code'];
                    } catch (\Exception $e) {
                        $values[$field->getTableColumnName()] = 'REPLACE_BY_REAL_CODE';
                    }
                } else {
                    $field->setRandomValue(1);
                    $values[$field->getTableColumnName()] = $field->getRandomValue();
                }
            } else {
                $values[$field->getTableColumnName()] = $fixtures[$config->getEntityName()][$field->getName()]['value'];
            }
        }

        $sqlEntity = [
            'schema' => $config->getSchema(),
            'tableName' => $config->getTableName(),
            'columns' => $columns,
            'values' => [$values],
        ];

//        if (null !== $sqlParent) {
//            foreach ($sqlParent as $key => $value) {
//                $tables[] = $value;
//            }
//        }

        $tables[] = $sqlEntity;

        return $tables;
    }

    /**
     * @param EntityConfiguration $config
     *
     * @return array
     */
    protected function generateFixtures(EntityConfiguration $config): array
    {
        $fields = $config->getFields();
        $values = [];
        $fixtures = [];
        $parentFixtures = null;

//        if ($parent = $config->getParentEntity()) {
//            $parentConfig = EntityConfigLoader::findAndCreateFromEntityName($parent->getEntityName(), $config->getBundleName());
//            if(null !== $parentFixtures) {
//                $parentFixtures = $this->generateFixtures($parentConfig);
//            }
//        }

        foreach ($fields as $field) {
            if (!$field->isNativeType()) {
                // Referential ?
                if ($field->isReferential()) {
                    try {
                        $instances = $this->getDoctrine()->getRepository($field->getEntityType())->findAll();
                        if (count($instances)) {
                            $serializer = $this->container->get('serializer');
                            $data = $serializer->serialize($instances[0], 'json', ['groups' => 'referential_short']);
                            $values[$field->getName()]['value'] = json_decode($data, true);
                            $values[$field->getName()]['type'] = 'entity';
                        } else {
                            $values[$field->getName()]['value'] = '';
                            $values[$field->getName()]['type'] = 'string';
                        }
                    } catch (\Exception $e) {
                        $values[$field->getName()]['value'] = '';
                        $values[$field->getName()]['type'] = 'string';
                    }
                } else {
                    //TODO
//                $config = EntityConfigLoader::findAndCreateFromEntityName($field->getEntityClassName());
//                $tables[] = prepareSqlInsertData($config)
                    $values[$field->getName()]['value'] = $field->getRandomValue(true);
                    $values[$field->getName()]['type'] = $field->getType();
                }
            } else {
                $values[$field->getName()]['value'] = $field->getRandomValue(true);
                $values[$field->getName()]['type'] = $field->getType();
            }
            $values[$field->getName()]['field'] = $field;
        }

        if (null !== $parentFixtures) {
            foreach ($parentFixtures as $key => $value) {
                $fixtures[$key] = $value;
            }
        }

        $fixtures[$config->getEntityName()] = $values;

        return $fixtures;
    }

    /**
     * @return string
     */
    protected function getContextDirectoryPath(): string
    {
        return "tests/{$this->config->getBundleName()}/{$this->config->getContextNameForPath()}/";
    }

    /**
     * @return string
     */
    protected function getRouteNamePrefix(): string
    {
        $bundleName = $this->config->getBundleName();
        $prefix = str_replace(['API', 'Bundle'], ['api_', ''], $bundleName);
        $contextName = str_replace(['\\', '/'], '_', $this->config->getContextName());

        return CaseConverter::convertToPascalCase("{$prefix}_{$contextName}");
    }

    /**
     * @return string
     */
    public function getEntityTestsDirectory(): string
    {
        return $this->getContextDirectoryPath().$this->getConfig()->getEntityName().'/';
    }

    protected function generateDataSubDirectoryPath(): string
    {
        $contextDirectory = ucfirst(strtolower($this->getConfig()->getContextNameForPath()));

        return "{$this->config->getBundleName()}/{$contextDirectory}/";
    }

    /**
     * Directory for yml data fixtures configuration file
     *
     * @return string
     */
    public function generateDataYmlDirectoryPath(): string
    {
        return self::DATA_CSV_PATH.$this->generateDataSubDirectoryPath();
    }

    /**
     * Directory for csv fixtures
     *
     * @return string
     */
    public function generateDataCsvDirectoryPath(): string
    {
        return "{$this->generateDataYmlDirectoryPath()}{$this->config->getEntityName()}/";
    }

    /**
     * Creates and returns a Form instance from the type of the form.
     *
     * @param string $type    The fully qualified class name of the form type
     * @param mixed  $data    The initial data for the form
     * @param array  $options Options for the form
     *
     * @return FormInterface
     *
     * @final since version 3.4
     */
    protected function createForm($type, $data = null, array $options = array()): FormInterface
    {
        return $this->container->get('form.factory')->create($type, $data, $options);
    }
}
