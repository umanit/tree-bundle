<?php

namespace Umanit\Bundle\TreeBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode    = $treeBuilder->root('umanit_tree');

        $rootNode
            ->children()
                ->scalarNode('locale')->defaultValue('%locale%')->end()
                ->scalarNode('root_class')->defaultValue('\Umanit\Bundle\TreeBundle\Entity\RootEntity')->end()
                ->arrayNode('controllers_by_class')->info('Defines a controller to call by class')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('class')->isRequired()->end()
                            ->scalarNode('controller')->isRequired()->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('seo')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('redirect_301')->defaultValue(true)->end()
                        ->scalarNode('default_title')->defaultValue('Umanit Tree')->end()
                        ->scalarNode('default_description')->defaultValue('Umanit tree bundle')->isRequired()->end()
                        ->scalarNode('default_keywords')->defaultValue('web, bundle, symfony2')->isRequired()->end()
                        ->scalarNode('translation_domain')->defaultValue('messages')->end()
                    ->end()
                ->end()
                ->arrayNode('breadcrumb')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('root_name')->defaultValue('Home')->end()
                        ->scalarNode('translation_domain')->defaultValue('messages')->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
