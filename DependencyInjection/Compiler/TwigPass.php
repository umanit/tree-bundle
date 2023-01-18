<?php

declare(strict_types=1);

namespace Umanit\TreeBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class TwigPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (false === $container->hasDefinition('twig')) {
            return;
        }

        $definition = $container->getDefinition('twig');
        $definition->addMethodCall('addGlobal', ['umanit_tree_admin_layout', $container->getParameter('umanit_tree.admin_layout')]);
    }
}