<?php


namespace EasyApiBundle\Util\Maker;

use Doctrine\Common\Persistence\ManagerRegistry;
use EasyApiBundle\Model\Maker\EntityConfiguration;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Kernel;

class AbstractGenerator
{
    public const DEFAULT_SKELETON_PATH = '@EasyApiBundle/Resources/skeleton/';

    /**
     * @var string
     */
    protected static $templatesDirectory;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var EntityConfiguration
     */
    protected $config;

    /**
     * AbstractGenerator constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return ContainerInterface
     */
    protected function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * @return EntityConfiguration
     */
    protected function getConfig(): EntityConfiguration
    {
        return $this->config;
    }

    /**
     * @param string $bundle
     * @param string $entityName
     * @param string $tableName
     * @param string|null $parentEntityName
     * @param string|null $inheritanceType
     * @param string|null $context
     * @return mixed|EntityConfiguration
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function loadEntityConfigFromDatabase(string $bundle ,string $entityName, string $tableName, string $parentEntityName = null, string $inheritanceType = null, string $context =null)
    {
        $this->config = EntityConfigLoader::createEntityConfigFromDatabase($this->getDoctrine()->getManager(), $bundle, $entityName, $tableName, $parentEntityName, $inheritanceType, $context);

        return $this->config;
    }

    /**
     * @param string $entityName
     * @param string|null $bundle
     * @param string|null $context
     * @return EntityConfiguration|null
     */
    protected function loadEntityConfig(string $entityName, string $bundle = null, string $context = null)
    {
        $this->config = EntityConfigLoader::findAndCreateFromEntityName($entityName, $bundle);

        //        if ($parentEntityName) {
//            $parentConfig = EntityConfigLoader::findAndCreateFromEntityName($parentEntityName, $bundle);
//            $this->config->setParentEntity($parentConfig);
//        }

        return $this->config;
    }

    /**
     * @return string
     */
    protected static function getConsoleCommand()
    {
        return (Kernel::MAJOR_VERSION > 2) ? 'bin/console' : 'app/console';
    }

    /**
     * @param $directory string
     * @param $filename string
     * @param $fileContent string
     * @param $dumpExistingFiles boolean
     *
     * @return string
     */
    protected function writeFile($directory, $filename, $fileContent, $dumpExistingFiles = false)
    {
        $destinationFile = "$directory$filename";

        // create directory if necessary
        if (!is_dir($directory) && !mkdir($directory, 0755, true)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $directory));
        }

        if ($dumpExistingFiles && file_exists($destinationFile)) {
            rename($destinationFile, $destinationFile.'.old');
        }

        file_put_contents($destinationFile, $fileContent);

        return $destinationFile;
    }

    /**
     * @return string
     */
    protected function getSkeletonPath()
    {
        $configPath = $this->container->get('generator_skeleton_path', null);

        return $configPath ?? self::DEFAULT_SKELETON_PATH;
    }

    /**
     * @param string $templateName
     *
     * @return string
     */
    protected function getTemplatePath(string $templateName): string
    {
        return $this->getSkeletonPath().$templateName;
    }

    /**
     * Return the path of the entity.
     *
     * @param string $bundle
     * @param string $context
     *
     * @return string
     */
    protected function generateEntityFolderPath($bundle, $context)
    {
        return "src/{$bundle}/Entity/{$context}/";
    }

    /**
     * Return the path of the entity.
     *
     * @param $entityName
     *
     * @return string
     */
    protected function findEntityFolderPath($bundle, $context, $entityName)
    {
        // TODO verify if the file exist ,
        return $this->generateEntityFolderPath($bundle, $context);
    }

    /**
     * Shortcut to return the Doctrine Registry service.
     *
     * @return ManagerRegistry
     *
     * @throws \LogicException If DoctrineBundle is not available
     *
     * @final since version 3.4
     */
    protected function getDoctrine()
    {
        if (!$this->container->has('doctrine')) {
            throw new \LogicException('The DoctrineBundle is not registered in your application. Try running "composer require symfony/orm-pack".');
        }

        return $this->container->get('doctrine');
    }

    /**
     * @return array
     */
    public static function getProjectBundles()
    {
        $dir = 'src/';
        $bundles = [];
        $files = scandir($dir);

        foreach ($files as $file) {
            if (is_dir($dir.DIRECTORY_SEPARATOR.$file)) {
                $bundles[] = $file;
            }
        }

        return $bundles;
    }

    /**
     * @return string
     */
    public static function findTypeFile($typeName, $bundle)
    {
        $dir = "src/{$bundle}/Form/Type";

        return self::findFileRecursive($dir, $typeName);
    }

    /**
     * @param $entityName
     * @param $bundle
     *
     * @return string|null
     */
    public static function findEntityFile($entityName, $bundle)
    {
        $dir = "src/{$bundle}/Entity";

        return self::findFileRecursive($dir, "{$entityName}.php");
    }

    /**
     * @param $path
     * @param $filename
     * @param int $flags
     *
     * @return string|null
     */
    protected static function findFileRecursive($path, $filename, $flags = 0)
    {
        $files = scandir($path);
        foreach ($files as $file) {
            if ('.' !== $file && '..' !== $file && is_dir($path.DIRECTORY_SEPARATOR.$file)) {
                $findFiles = self::findFileRecursive($path.DIRECTORY_SEPARATOR.$file, $filename, $flags = 0);
                if (null !== $findFiles) {
                    return $findFiles;
                }
            } elseif ($file === $filename) {
                return $path.DIRECTORY_SEPARATOR.$file;
            }
        }

        return null;
    }
}