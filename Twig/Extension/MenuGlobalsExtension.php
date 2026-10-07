<?php

namespace Umanit\TreeBundle\Twig\Extension;

use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use Umanit\TreeBundle\Menu\MenuBuilder;

/**
 * Exposes the `menus` global lazily, when Twig initializes its extensions.
 *
 * Calling `Environment::addGlobal()` during the request fails with a LogicException as soon as Twig
 * has already been initialized (e.g. by an earlier listener or an error page).
 */
class MenuGlobalsExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(
        private RequestStack $requestStack,
        private MenuBuilder $menuBuilder,
    ) {
    }

    public function getGlobals(): array
    {
        $request = $this->requestStack->getMainRequest();

        return [
            'menus' => null === $request ? [] : $this->menuBuilder->getMenus($request->getLocale()),
        ];
    }
}
