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

        return $content;
    }

    /**
     * @param $bundle string
     * @param $context string
     * @param $entityName string
     * @param $dumpExistingFiles boolean
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
     */
    protected function updateEntity(string $type, bool $dumpExistingFiles = false)
    {
        switch ($type){
            case 'annotations':
                return $this->updateEntityClass($dumpExistingFiles);
            case 'yaml':
                return $this->updateYamlConfig($dumpExistingFiles);
        }
    }

    /**
     * @param bool $dumpExistingFiles
     * @return string
     */
    protected function updateEntityClass(bool $dumpExistingFiles = false)
    {
        $file = '';
        $content = file_get_contents($this->config->getEntityFileClassPath());
        $repositoryClass = $this->config->getRepositoryClass();

        if(preg_match('/@ORM\\\Entity\(\)/', $content)) {
            $content = str_replace('@ORM\\Entity()', "@ORM\Entity(repositoryClass=\"{$repositoryClass}\")", $content);
            $file = $this->writeFile($this->config->getEntityFileClassDirectory(), $this->config->getEntityFileClassName(), $content, $dumpExistingFiles);
        } elseif(preg_match('/@ORM\\\Entity\(.*repositoryClass="(.+)"\)/', $content, $matches)) {
            $content = str_replace($matches[1], $repositoryClass, $content);
            $file = $this->writeFile($this->config->getEntityFileClassDirectory(), $this->config->getEntityFileClassName(), $content, $dumpExistingFiles);
        }

        return $this->container->getParameter('kernel.project_dir').'/'.$file;
    }

//    protected function updateYamlConfig($dumpExistingFiles)
//    {
//        // Modify the doctrine config to activate it
//        $files[] = $ymlFilePath = static::generateYamlFilePath($bundle, $context, $entityName);
//        $repositoryPath = "$bundle\\\\Repository\\\\$context\\\\$entityName".'Repository';
//        $ymlContent = file_get_contents($ymlFilePath);
//
//        // move file
//        if ($dumpExistingFiles && file_exists($ymlFilePath)) {
//            rename($ymlFilePath, "{$ymlFilePath}.old");
//        }
//
//        if (!preg_match('/repositoryClass: [a-zA-Z0-9_\\\\]+/', $ymlContent)) {
//            file_put_contents($ymlFilePath, preg_replace('/table: ([a-zA-Z0-9_]+)/', "table: $1\n    repositoryClass: $repositoryPath", $ymlContent));
//        } else {
//            file_put_contents($ymlFilePath, preg_replace('/repositoryClass: ([a-zA-Z0-9_\\\\]+)/', "repositoryClass: $repositoryPath", $ymlContent));
//        }
//    }
}