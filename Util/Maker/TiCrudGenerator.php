<?php


namespace EasyApiBundle\Util\Maker;

use EasyApiBundle\Model\Maker\EntityConfiguration;
use EasyApiBundle\Util\Forms\FormSerializer;
use EasyApiBundle\Util\StringUtils\CaseConverter;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Serializer\Serializer;

class TiCrudGenerator extends AbstractGenerator
{
    /**
     * @var string
     */
    protected const DATA_CSV_PATH = 'tests/data/csv/';

    /**
     * @var string
     */
    protected static $templatesDirectory = 'Doctrine/TI/';

    /**
     * @var array
     */
    private static $content;

    /**
     * @var array
     */
    private static $sqlContent;

    /**
     * @param string $bundle
     * @param string $context
     * @param string $entityName
     * @param string $parent
     * @param bool $dumpExistingFiles boolean
     *
     * @return array paths to the generated files
     */
    public function generate(string $bundle, string $context, string $entityName, string $parent = null, bool $dumpExistingFiles = false)
    {
        $this->config = $this->loadEntityConfig($entityName, $bundle, $context);

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
    protected function generateGetTests($dumpExistingFiles = false)
    {
        $fileContent = $this->getContainer()->get('templating')->render(
            $this->getTemplatePath('crud_get.php.twig'),
            $this->generateContent()
        );

        return $this->writeFile($this->getEntityTestsDirectory(), 'GET'.$this->config->getEntityName().'Test.php', $fileContent, $dumpExistingFiles);
    }

    /**
     * Generate GetList service tests.
     *
     * @param $dumpExistingFiles boolean
     *
     * @return string
     */
    protected function generateGetListTests($dumpExistingFiles = false)
    {
        $fileContent = $this->getContainer()->get('templating')->render(
            $this->getTemplatePath('crud_get_list.php.twig'),
            $this->generateContent()
        );

        return $this->writeFile($this->getEntityTestsDirectory(), 'GETLIST'.$this->config->getEntityName().'Test.php', $fileContent, $dumpExistingFiles);
    }

    /**
     * Generate DescribeForm service tests.
     *
     * @param $dumpExistingFiles boolean
     *
     * @return string
     */
    protected function generateDescribeFormTests($dumpExistingFiles = false)
    {
        $fileContent = $this->getContainer()->get('templating')->render(
            $this->getTemplatePath('crud_describe_form.php.twig'),
            $this->generateContent()
        );

        return $this->writeFile($this->getEntityTestsDirectory(), 'GETDESCRIBEFORM'.$this->config->getEntityName().'Test.php', $fileContent, $dumpExistingFiles);
    }

    /**
     * Generate Delete service tests.
     *
     * @param $dumpExistingFiles boolean
     *
     * @return string
     */
    protected function generateDeleteTests($dumpExistingFiles = false)
    {
        $fileContent = $this->getContainer()->get('templating')->render(
            $this->getTemplatePath('crud_delete.php.twig'),
            $this->generateContent()
        );

        return $this->writeFile($this->getEntityTestsDirectory(), 'DELETE'.$this->config->getEntityName().'Test.php', $fileContent, $dumpExistingFiles);
    }

    /**
     * Generate Post service tests.
     *
     * @param $dumpExistingFiles boolean
     *
     * @return string
     */
    protected function generatePostTests($dumpExistingFiles = false)
    {
        $fileContent = $this->getContainer()->get('templating')->render(
            $this->getTemplatePath('crud_post.php.twig'),
            $this->generateContent()
        );

        return $this->writeFile($this->getEntityTestsDirectory(), 'POST'.$this->config->getEntityName().'Test.php', $fileContent, $dumpExistingFiles);
    }

    /**
     * Generate Put service tests.
     *
     * @param $dumpExistingFiles boolean
     *
     * @return string
     */
    protected function generatePutTests($dumpExistingFiles = false)
    {
        $fileContent = $this->getContainer()->get('templating')->render(
            $this->getTemplatePath('crud_put.php.twig'),
            $this->generateContent()
        );

        return $this->writeFile($this->getEntityTestsDirectory(), 'PUT'.$this->config->getEntityName().'Test.php', $fileContent, $dumpExistingFiles);
    }

    /**
     * @param $dumpExistingFiles boolean
     *
     * @return string
     */
    protected function generateAbstractContext($dumpExistingFiles = false)
    {
        $abstractContextName = $this->getAbstractContextTestName();
        $fileContent = $this->getContainer()->get('templating')->render(
            $this->getTemplatePath('abstract_context.php.twig'),
            $this->generateContent()
        );

        return $this->writeFile($this->getEntityTestsDirectory(), "{$abstractContextName}.php", $fileContent, $dumpExistingFiles);
    }

    /**
     * @return string
     */
    public function getAbstractContextTestName()
    {
        $contextName = str_replace(['\\', '/'], '', $this->config->getContextName());

        return "Abstract{$contextName}{$this->config->getEntityName()}Test";
    }

    /**
     * @param array $fixtures
     * @param bool $dumpExistingFiles
     *
     * @return string
     * @throws \Twig\Error\Error
     */
    protected function generateSql(array $fixtures, bool $dumpExistingFiles = false)
    {
        $fileContent = $this->getContainer()->get('templating')->render(
            $this->getTemplatePath('crud_datas.sql.twig'),
            $this->generateSqlContent($fixtures)
        );

        return $this->writeFile($this->generateSqlDirectory(), $this->config->getEntityName().'.sql', $fileContent, $dumpExistingFiles);
    }

    /**
     * @param array $fixtures
     * @param bool $dumpExistingFiles
     * @return bool
     * @throws \Twig\Error\Error
     */
    protected function generateCsv(array $fixtures, bool $dumpExistingFiles = false)
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

            $csvWriteOperation .= '    '.$this->writeFile($directory, $table['tableName'].'.csv', $fileContent, $dumpExistingFiles)."\n";
        }

        return "{$ymlWriteOperation}\n{$csvWriteOperation}";
    }

    /**
     * @param array $dataFixtures
     * @param bool $dumpExistingFiles
     * @return string
     * @throws \Twig\Error\Error
     */
    protected function generateDataYml(array $dataFixtures, bool $dumpExistingFiles = false)
    {
        $ymlData = [];
        $path = str_replace(self::DATA_CSV_PATH, '', $this->generateDataCsvDirectoryPath());

        foreach ($dataFixtures['tables'] as $table) {
            $ymlData[] = [ 'filePath' => "{$path}{$table['tableName']}.csv", 'tableName' => $table['tableName']];
        }

        // yml content
        $fileContent = $this->getContainer()->get('templating')->render(
            $this->getTemplatePath('crud_data.yml.twig'),
            ['files' => $ymlData]
        );

        return $this->writeFile($this->generateDataYmlDirectoryPath(), $this->generateDataYmlFilename(), $fileContent, $dumpExistingFiles);
    }

    /**
     * @return string
     */
    protected function generateDataYmlFilename()
    {
        return $this->config->getEntityName().'.yml';
    }

    /**
     * Return parameters for the template.
     *
     * @return array
     */
    protected function generateContent()
    {
        if (empty(static::$content)) {
            $idColumnName = $this->config->isReferential() ? 'code' : 'id';

            // fixtures
            $fixtures = [
                'get' => $this->generateFixtures($this->config),
                'get2' => $this->generateFixtures($this->config),
                'post' => $this->generateFixtures($this->config),
            ];

            $fixtures['put'] = $fixtures['get'];
            $fixtures['delete'] = $fixtures['get'];

            if ($parent = $this->config->getParentEntity()) {
                $parentIdColumnName = $this->config->isReferential() ? 'code' : 'id';
                $fixtures['id1'] = $fixtures['get'][$parent->getEntityName()][$parentIdColumnName]['value'];
                $fixtures['get2'][$parent->getEntityName()][$parentIdColumnName]['value'] = '2';
                $fixtures['id2'] = $fixtures['get2'][$parent->getEntityName()][$parentIdColumnName]['value'];
            } else {
                $fixtures['id1'] = $fixtures['get'][$this->config->getEntityName()][$idColumnName]['value'];
                $fixtures['get2'][$this->config->getEntityName()][$idColumnName]['value'] = '2';
                $fixtures['id2'] = $fixtures['get2'][$this->config->getEntityName()][$idColumnName]['value'];
            }

            // form
            $describer = new FormSerializer($this->container->get('form.factory'), $this->container->get('router'));
            $formName = $this->config->getBundleName().'\\Form\\Type\\'.$this->config->getContextName().'\\'.$this->config->getEntityName().'Type';
            $form = $describer->normalize($this->createForm($formName));
            $formFields = [];
            foreach ($form->getFields() as $field) {
                $formFields[] = $field->getName();
            }

            // requiredFieldsListing
            $requiredFieldsForArray = [];
            $requiredFormFields = [];
            foreach ($form->getFields() as $field) {
                if ($field->isRequired()) {
                    $requiredFieldsForArray[] = "'{$field->getName()}'";
                    $requiredFormFields[] = $field->getName();
                }
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
                'route_name_get' => $this->getRouteNamePrefix().'_'.CaseConverter::convertToPascalCase($this->config->getEntityName()).'_get',
                'route_name_list' => $this->getRouteNamePrefix().'_'.CaseConverter::convertToPascalCase($this->config->getEntityName()).'_list',
                'route_name_post' => $this->getRouteNamePrefix().'_'.CaseConverter::convertToPascalCase($this->config->getEntityName()).'_create',
                'route_name_put' => $this->getRouteNamePrefix().'_'.CaseConverter::convertToPascalCase($this->config->getEntityName()).'_update',
                'route_name_delete' => $this->getRouteNamePrefix().'_'.CaseConverter::convertToPascalCase($this->config->getEntityName()).'_delete',
                'route_name_describe_form' => $this->getRouteNamePrefix().'_'.CaseConverter::convertToPascalCase($this->config->getEntityName()).'_describe_form',
                'fields' => $this->config->getFields(),
                'requiredFieldsForArray' => implode(', ', $requiredFieldsForArray),
                'fixtures' => $fixtures,
                'error_context' => lcfirst($this->config->getEntityName()),
                'form' => $form,
                'formFields' => $formFields,
                'requiredFormFields' => $requiredFormFields,
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
    protected function generateSqlContent(array $fixtures)
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
    protected function prepareSqlInsertData(EntityConfiguration $config, array $fixtures, EntityConfiguration $childEntityConfig = null)
    {
        $columns = [];
        $values = [];
        $fields = $config->getFields();
        $tables = [];
        $sqlParent = null;

        // Has parent class ?
        if ($parent = $config->getParentEntity()) {
            $parentConfig = EntityConfigLoader::findAndCreateFromEntityName($parent->getEntityName(), $config->getBundleName());
            $sqlParent = $this->prepareSqlInsertData($parentConfig, $fixtures, $config);
            $columnIdName = $config->isReferential() ? 'code' : 'id';
            $columns[] = $columnIdName;
            $values['id'] = $fixtures['get'][$parentConfig->getEntityName()][$columnIdName]['value'];
            $values2['id'] = $fixtures['get2'][$parentConfig->getEntityName()][$columnIdName]['value'];
        }

        // is parent class ?
        // Inheritance Type column
        if ($config->isParentEntity() && null !== $childEntityConfig) {
            $columns[] = '`'.EntityConfiguration::inheritanceTypeColumnName.'`';
            $values[EntityConfiguration::inheritanceTypeColumnName] = '\''.$childEntityConfig->getEntityName().'\'';
            $values2[EntityConfiguration::inheritanceTypeColumnName] = $values[EntityConfiguration::inheritanceTypeColumnName];
        }

        // Other fields
        foreach ($fields as $field) {
            $columns[] = $field->getTableColumnName();
            if (!$field->isNativeType()) {
                // Referential ?
                if ($field->isReferential()) {
                    try {
                        $values[$field->getTableColumnName()] = $fixtures['get'][$config->getEntityName()][$field->getName()]['value']['code'];
                        $values2[$field->getTableColumnName()] = $fixtures['get2'][$config->getEntityName()][$field->getName()]['value']['code'];
                    } catch (\Exception $e) {
                        $values[$field->getTableColumnName()] = 'REPLACE_BY_REAL_CODE';
                    }
                } else {
                    $field->setRandomValue(1);
                    $foreignConfig = EntityConfigLoader::findAndCreateFromEntityName($field->getEntityClassName());
                    $tables[] = [
                        'tableName' => (null !== $foreignConfig) ? $foreignConfig->getTableName() : str_replace('_id', '', $field->getTableColumnName()),
                        'columns' => ['id', 'created_at', 'updated_at'],
                        'values' => [['1', '2018-05-02 12:11:10', '2018-05-02 12:11:10'], ['2', '2018-05-02 12:11:10', '2018-05-02 12:11:10']],
                    ];

                    $values[$field->getTableColumnName()] = $field->getRandomValue();
                    $values2[$field->getTableColumnName()] = $field->getRandomValue();
                }
            } else {
                $values[$field->getTableColumnName()] = $fixtures['get'][$config->getEntityName()][$field->getName()]['value'];
                $values2[$field->getTableColumnName()] = $fixtures['get2'][$config->getEntityName()][$field->getName()]['value'];
            }
        }

        $sqlEntity = [
            'tableName' => $config->getTableName(),
            'columns' => $columns,
            'values' => [$values, $values2],
        ];

        if (null !== $sqlParent) {
            foreach ($sqlParent as $key => $value) {
                $tables[] = $value;
            }
        }

        $tables[] = $sqlEntity;

        return $tables;
    }

    /**
     * @param EntityConfiguration $config
     *
     * @return array
     */
    protected function generateFixtures(EntityConfiguration $config)
    {
        $fields = $config->getFields();
        $values = [];
        $fixtures = [];
        $parentFixtures = null;

        if ($parent = $config->getParentEntity()) {
            $parentConfig = EntityConfigLoader::findAndCreateFromEntityName($parent->getEntityName(), $config->getBundleName());
            $parentFixtures = $this->generateFixtures($parentConfig);
        }

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
    protected function getContextDirectoryPath()
    {
        $context = $this->config->getContextNameForPath();

        return 'tests/'.$this->config->getBundleName()."/{$context}/";
    }

    /**
     * @return string
     */
    protected function getRouteNamePrefix()
    {
        $bundleName = $this->config->getBundleName();
        $prefix = str_replace(['API', 'Bundle'], ['api_', ''], $bundleName);
        $contextName = str_replace(['\\', '/'], '_', $this->config->getContextName());

        return CaseConverter::convertToPascalCase("{$prefix}_{$contextName}");
    }

    /**
     * @return string
     */
    public function getEntityTestsDirectory()
    {
        return $this->getContextDirectoryPath().$this->getConfig()->getEntityName().'/';
    }

    /**
     * @return string
     */
    public function generateSqlDirectory()
    {
        $sqlBundleDirectory = str_replace(['api', 'bundle'], ['', ''], strtolower($this->config->getBundleName()));

        return "tests/sql/{$sqlBundleDirectory}/".strtolower($this->getConfig()->getContextNameForPath()).'/';
    }

    protected function generateDataSubDirectoryPath()
    {
        $contextDirectory = ucfirst(strtolower($this->getConfig()->getContextNameForPath()));

        return "{$this->config->getBundleName()}/{$contextDirectory}/";
    }

    /**
     * Directory for yml data fixtures configuration file
     *
     * @return string
     */
    public function generateDataYmlDirectoryPath()
    {
        return self::DATA_CSV_PATH.$this->generateDataSubDirectoryPath();
    }

    /**
     * Directory for csv fixtures
     *
     * @return string
     */
    public function generateDataCsvDirectoryPath()
    {
        return $this->generateDataYmlDirectoryPath().$this->config->getEntityName().'/';
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
    protected function createForm($type, $data = null, array $options = array())
    {
        return $this->container->get('form.factory')->create($type, $data, $options);
    }
}