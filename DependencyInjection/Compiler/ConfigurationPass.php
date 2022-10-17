<?php

namespace EasyApiBundle\DependencyInjection\Compiler;

use EasyApiBundle\Util\ApiDoc\OpenApiPhpDescriber;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Override annotation model reading
 * Will be removed when Doctrine will support self & static constant in annotations.
 */
final class ConfigurationPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $nelmioApiDocConfig = $container->getParameter('nelmio_api_doc.areas');
        foreach ($nelmioApiDocConfig as $area) {
            $container->register("nelmio_api_doc.nelmio_open_api_php.{$area}", OpenApiPhpDescriber::class)
                ->setPublic(false)
                ->setArguments([
                    new Reference("nelmio_api_doc.routes.{$area}"),
                    new Reference('nelmio_api_doc.controller_reflector'),
                    new Reference('annotation_reader'),
                    new Reference('logger'),
                ])
                ->addTag("nelmio_api_doc.describer.{$area}");
        }
    }
}
