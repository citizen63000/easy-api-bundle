<?php

namespace EasyApiBundle\DependencyInjection;

use FOS\UserBundle\Form\Type;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This class contains the configuration information for the bundle.
 *
 * This information is solely responsible for how the different configuration
 * sections are normalized, and merged.
 *
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('easy_api');
        $rootNode
            ->children()

                ->booleanNode('authentication')->defaultTrue()->end()

                ->scalarNode('user_class')->isRequired()->cannotBeEmpty()->end()

                ->arrayNode('user_tracking')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enable')->defaultFalse()->end()
                        ->scalarNode('connection_history_class')->defaultNull()->end()
                    ->end()
                    ->validate()
                        ->ifTrue(function ($v) { return $v['enable'] && null === $v['connection_history_class'];})
                        ->thenInvalid('You need to specify connection_history_class.')
                    ->end()
                ->end()

                ->scalarNode('reset_password_mail_template')->defaultValue('@EasyApi/mail/reset_password_mail.html.twig')->end()

            ->arrayNode('inheritance')
                ->addDefaultsIfNotSet()
                ->children()
            ->scalarNode('generator_skeleton_path')->defaultValue('@EasyApiBundle/Resources/skeleton/')->end()
            ->scalarNode('entity')->defaultValue('EasyApiBundle\Entity\AbstractBaseEntity')->end()
                    ->scalarNode('entity_referential')->defaultValue('EasyApiBundle\Entity\AbstractBaseReferential')->end()
                    ->scalarNode('form')->defaultValue('EasyApiBundle\Form\Type\AbstractApiType')->end()
                    ->scalarNode('repository')->defaultValue('EasyApiBundle\Util\AbstractRepository')->end()
                    ->scalarNode('controller')->defaultValue('EasyApiBundle\Util\Controller\AbstractApiController')->end()
                ->end()
            ->end()

            ->end()
            ;

        return $treeBuilder;
    }
}
