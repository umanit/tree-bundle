<?php

namespace Umanit\TreeBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Umanit\TreeBundle\DependencyInjection\Compiler\TwigPass;

class UmanitTreeBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new TwigPass);
    }
}
