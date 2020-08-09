<?php

namespace EasyApiBundle;

use EasyApiBundle\DependencyInjection\Compiler\ConfigurationPass;
//use EasyApiBundle\DependencyInjection\EasyApiExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class EasyApiBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        //        $container->registerExtension(new EasyApiExtension());

        $container->addCompilerPass(new ConfigurationPass());

    }
}