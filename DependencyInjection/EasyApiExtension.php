<?php

namespace EasyApiBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class EasyApiExtension extends Extension
{
    /**
     * @inheritDoc
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        // Load configuration
        $config = (new Processor())->processConfiguration(new Configuration(), $configs);

        // Convert config as parameters
        $this->loadParametersFromConfiguration($config, $container);

        // Load services
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');
    }

    /**
     * @param array $loadedConfig
     * @param ContainerBuilder $container
     * @param string $parentKey
     */
    protected function loadParametersFromConfiguration(array $loadedConfig, ContainerBuilder $container, string $parentKey = 'easy_api')
    {
        foreach ($loadedConfig as $parameter => $value) {
            if (is_array($value)) {
                $this->loadParametersFromConfiguration($value, $container, "{$parentKey}.{$parameter}");
            } else {
                $container->setParameter("{$parentKey}.{$parameter}", $value);
            }
        }
    }
}