<?php

namespace EasyApiBundle\Util\Maker;

use EasyApiBundle\Model\Maker\EntityConfiguration;

class RepositoryGenerator extends AbstractGenerator
{
    /**
     * @return array
     */
    protected function generateContent()
    {
        $content = [];

        $content['class_name'] = $this->config->getEntityName().'Repository';
        $content['namespace'] = $this->config->getBundleName().'\Repository'.($this->config->getContextName() ? '\\'.$this->config->getContextName() : '');

        $content['uses'] = [];
        $content['uses'][] = $this->container->getParameter('easy_api.inheritance.repository');
        $content['parent'] = EntityConfiguration::getEntityNameFromNamespace($this->container->getParameter('easy_api.inheritance.repository'));

        return $content;
    }

    /**
     * @param string $bundle
     * @param string $entityName
     * @param string|null $context
     * @param bool $dumpExistingFiles
     *
     * @return array paths to the generated files
     */
    public function generate(string $bundle, string $entityName, ?string $context, bool $dumpExistingFiles = false)
    {
        $this->config = $this->loadEntityConfig($entityName, $bundle, $context);
        $path = $this->config->getBundleName().'/Repository/'.($this->config->getContextName() ? $this->config->getContextName().'/' : '');
        $this->config->setRepositoryClass(str_replace('/', '\\', $path).$this->config->getEntityName().'Repository');
        $filename = $this->config->getEntityName().'Repository.php';

        $files = [];

        // Create entityRepository file
        $fileContent = $this->getContainer()->get('templating')->render(
            '@EasyApiBundle/Resources/skeleton/doctrine/entity_repository.php.twig',
            $this->generateContent()
        );

        $files[] = "{$this->container->getParameter('kernel.project_dir')}/".$this->writeFile("src/{$path}", $filename, $fileContent, $dumpExistingFiles);

        $files[] = $this->updateEntity( 'annotations', $dumpExistingFiles);

        return $files;
    }

    /**+
     * @param string $type
     * @param bool $dumpExistingFiles
     * @return string
     */
    protected function updateEntity(string $type, bool $dumpExistingFiles = false)
    {
        switch ($type){
            case 'annotations':
                return $this->updateEntityClass($dumpExistingFiles);
        }
    }

    /**
     * @param bool $dumpExistingFiles
     * @return string
     */
    protected function updateEntityClass(bool $dumpExistingFiles = false): string
    {
        $content = file_get_contents($this->config->getEntityFileClassPath());
        $repositoryClass = $this->config->getRepositoryClass();

        if(preg_match('/@ORM\\\Entity\(\)/', $content)) {
            $content = str_replace('@ORM\\Entity()', "@ORM\Entity(repositoryClass=\"{$repositoryClass}\")", $content);
        } elseif(preg_match('/@ORM\\\Entity\(.*repositoryClass="(.+)"\)/', $content, $matches)) {
            $content = str_replace($matches[1], $repositoryClass, $content);
        }

        return $this->writeFile($this->config->getEntityFileClassDirectory(), $this->config->getEntityFileClassName(), $content, $dumpExistingFiles);;
    }
}