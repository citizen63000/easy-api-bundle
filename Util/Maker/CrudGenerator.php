<?php


namespace EasyApiBundle\Util\Maker;


use EasyApiBundle\Util\StringUtils\CaseConverter;

class CrudGenerator
{
    /**
     * @var string
     */
    protected static $templatesDirectory = 'Doctrine/';

    /**
     * @param $bundle string
     * @param $context string
     * @param $entityName string
     * @param $parentEntityName string
     * @param $dumpExistingFiles boolean
     *
     * @return array paths to the generated files
     *
     * @throws \Twig\Error\Error
     */
    public function generate($bundle, $context, $entityName, $parentEntityName, $dumpExistingFiles = false)
    {
        $this->loadDoctrineYamlConfig($bundle, $context, $entityName, $parentEntityName);
        $paths = [];
        $paths['controller'] = $this->generateController($dumpExistingFiles);
        $paths['routing'] = $this->generateRouting($dumpExistingFiles);

        return $paths;
    }

    /**
     * Generate Controller file.
     *
     * @param $dumpExistingFiles boolean
     *
     * @return string
     *
     * @throws \Twig\Error\Error
     */
    protected function generateController($dumpExistingFiles)
    {
        $fileContent = $this->getContainer()->get('templating')->render(
            $this->getTemplatePath('crud_controller.php.twig'),
            $this->generateContent()
        );

        return $this->writeFile($this->getControllerDirectoryPath(), $this->config->getEntityName().'Controller.php', $fileContent, $dumpExistingFiles);
    }

    /**
     * Generate routing file.
     *
     * @param $dumpExistingFiles boolean
     *
     * @return string
     *
     * @throws \Twig\Error\Error
     */
    protected function generateRouting($dumpExistingFiles)
    {
        // Generate general routing file
        $this->generateBundleRoutingFile($dumpExistingFiles);

        // Generate specific routing file
        $fileContent = $this->getContainer()->get('templating')->render(
            $this->getTemplatePath('crud_routing.yml.twig'),
            $this->generateContent()
        );

        return $this->writeFile($this->getRoutingDirectoryPath(), $this->config->getEntityName().'.yml', $fileContent, $dumpExistingFiles);
    }

    /**
     * Generate the general routing file of the bundle to link the specific routing file.
     *
     * @param $dumpExistingFiles
     *
     * @return bool|int
     */
    protected function generateBundleRoutingFile($dumpExistingFiles)
    {
        $bundleName = $this->config->getBundleName();
        $routingFilePath = "src/{$bundleName}/Resources/config/";
        $routingFile = "{$routingFilePath}routing.yml";
        $content = file_exists($routingFile) ? file_get_contents($routingFile) : '';
        $dataContent = $this->generateContent();
        $routeName = $dataContent['route_name_prefix'].'_'.strtolower($dataContent['entity_route_name']);

        try {
            if (!preg_match("/{$routeName}/", $content)) {
                $content .= "\n\n".$this->getContainer()->get('templating')->render(
                        $this->getTemplatePath('bundle_routing.yml.twig'),
                        $dataContent
                    );
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
        }

        return $this->writeFile($routingFilePath, 'routing.yml', $content, $dumpExistingFiles);
    }

    /**
     * @return string
     */
    protected function getControllerDirectoryPath()
    {
        $context = str_replace('\\', '/', $this->config->getContextName());

        return 'src/'.$this->config->getBundleName()."/Controller/{$context}/";
    }

    /**
     * @return string
     */
    protected function getRoutingDirectoryPath()
    {
        $context = str_replace('\\', '/', $this->config->getContextName());

        return 'src/'.$this->config->getBundleName()."/Resources/config/routing/$context/";
    }

    /**
     * @return string
     */
    protected function getRouteNamePrefix()
    {
        $bundleName = $this->config->getBundleName();
        $prefix = str_replace(['API', 'Bundle'], ['api_', ''], $bundleName);

        return CaseConverter::convertToPascalCase($prefix.'_'.str_replace(['\\', '/'], '_', $this->config->getContextName()));
    }

    /**
     * @return array
     */
    protected function generateContent()
    {
        $transformedContext = str_replace('\\', '/', $this->config->getContextName());

        $content = [
            'entity_name' => $this->config->getEntityName(),
            'bundle_name' => $this->config->getBundleName(),
            'routing_url' => "{$this->config->getBundleName()}/Resources/config/routing/{$transformedContext}/{$this->config->getEntityName()}.yml",
            'context_name' => str_replace('/', '\\', $this->config->getContextName()),
            'route_name_prefix' => $this->getRouteNamePrefix(),
            'entity_route_name' => CaseConverter::convertToPascalCase($this->config->getEntityName()),
            'entity_url_name' => str_replace('_', '-', CaseConverter::convertToPascalCase($this->config->getEntityName())),
            'serialization_groups' => implode(', ', $this->getSerializerGroups()),
        ];

        return $content;
    }

    /**
     * @return array
     */
    protected function getSerializerGroups()
    {
        $groups = ['"'.CaseConverter::convertToPascalCase($this->config->getEntityName()).'_full"'];

        // parent secrializer groups
        $parentConfig = $this->config->getParentEntity();
        if (null !== $parentConfig) {
            $groups[] = '"'.CaseConverter::convertToPascalCase($parentConfig->getEntityName()).'_full"';
            foreach ($parentConfig->getFields() as $field) {
                if ($field->isReferential() && !in_array('"referential_short"', $groups)) {
                    $groups[] = '"referential_short"';
                } elseif (!$field->isNativeType() && ('manyToOne' === $field->getRelationType() || 'oneToOne' === $field->getRelationType())) {
                    $groups[] = '"'.CaseConverter::convertToPascalCase($field->getName()).'_id"';
                }
            }
        }

        foreach ($this->config->getFields() as $field) {
            if ($field->isReferential() && !in_array('"referential_short"', $groups)) {
                $groups[] = '"referential_short"';
            } elseif (!$field->isNativeType() && ('manyToOne' === $field->getRelationType() || 'oneToOne' === $field->getRelationType())) {
                $groups[] = '"'.CaseConverter::convertToPascalCase($field->getName()).'_id"';
            }
        }

        return $groups;
    }
}