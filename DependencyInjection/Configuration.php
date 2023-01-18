<?php

namespace Umanit\TreeBundle\DependencyInjection;

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
        $treeBuilder = new TreeBuilder('umanit_tree');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->scalarNode('locale')->defaultValue('%locale%')->end()
                ->scalarNode('root_class')->defaultValue('Umanit\TreeBundle\Entity\RootEntity')->end()
                ->scalarNode('admin_layout')->defaultValue('@UmanitTree/admin/default_layout.html.twig')->end()
                ->scalarNode('menu_form_class')->defaultValue('Umanit\TreeBundle\Form\Type\MenuType')->end()
                ->scalarNode('menu_entity_class')->defaultNull()->end()
                ->arrayNode('menus')->info('Defines the menus in your application.')
                    ->prototype('scalar')->end()
                    ->defaultValue(['primary'])
                ->end()
                ->arrayNode('menus_roles')->info('Define the roles required to administrate the menus')
                    ->prototype('scalar')->end()
                    ->defaultValue(['ROLE_ADMIN'])
                ->end()
                ->arrayNode('node_types')->info('Configure each node type')
                    ->prototype('array')
                        ->validate()
                            ->ifTrue(function ($v) {
                                if (!\is_array($v)) {
                                    return true;
                                }
                                if (null === $v['template'] && $v['controller'] === 'Symfony\Bundle\FrameworkBundle\Controller\TemplateController:templateAction' && null === $v['api_controller']) {
                                    return true;
                                }

                                return false;
                            })
                            ->thenInvalid('You must define an api_controller, a controller or a template for your node_type"')
                            ->end()
                        ->children()
                            ->scalarNode('class')->isRequired()->end()
                            ->scalarNode('controller')->defaultValue('Symfony\Bundle\FrameworkBundle\Controller\TemplateController:templateAction')->end()
                            ->scalarNode('api_controller')->defaultNull()->info('Defines the api controller. Requires UmanitApiTreeBundle.')->end()
                            ->scalarNode('template')->defaultNull()->end()
                            ->booleanNode('menu')
                                ->info('Defines if the node should appear in the menu admin. Default is false.')
                                ->defaultFalse()
                            ->end()
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
