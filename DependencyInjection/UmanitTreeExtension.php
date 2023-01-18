<?php

namespace Umanit\TreeBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class UmanitTreeExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        // Set configuration into params
        $rootName = 'umanit_tree';
        $container->setParameter($rootName, $config);
        $this->setConfigAsParameters($container, $config, $rootName);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');
    }

    /**
     * Add config keys as parameters
     *
     * @param ContainerBuilder $container
     * @param array            $params
     * @param string           $parent
     */
    private function setConfigAsParameters(ContainerBuilder $container, array $params, $parent)
    {
        foreach ($params as $key => $value) {
            $name = $parent.'.'.$key;
            $container->setParameter($name, $value);

            if (is_array($value)) {
                $this->setConfigAsParameters($container, $value, $name);
            }
        }
    }

    /**
     * {@inheritdoc}
     *
     * @param ContainerBuilder $container
     */
    public function prepend(ContainerBuilder $container)
    {
        if ($container->hasExtension('sonata_admin')) {
            $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
            $loader->load('sonata_admin.yaml');
        }

        /*
         * We use a custom entity manager for the bundle to allow integration with umanit/block-bundle
         *
         * J'utilise un entity manager dédié par endroits car sinon ça vide les blocs de la page edito...
         * à cause du fait que l'UnitOfWork clear toutes les propriétés sauf la collectionDeletions
         * entre deux transactions. BlockBundle faisant un DELETE puis un INSERT des nodes, j'ai un DELETE
         * supplémentaire qui se jour dans le tour d'UnitOfWork impliqué dans ce listener qui vide donc les
         * blocs définitivement.
         */
        $container->prependExtensionConfig('doctrine', [
            'orm' => [
                'entity_managers' => [
                    'umanit_tree' => [
                        'connection' => 'default',
                        'mappings'   => [
                            'UmanitTree' => [
                                'type'   => 'attribute',
                                'dir'    => \dirname(__DIR__).'/Entity',
                                'prefix' => 'Umanit\TreeBundle\Entity',
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }
}
