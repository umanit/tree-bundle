<?php

namespace Umanit\TreeBundle\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;
use Umanit\TreeBundle\Menu\MenuBuilder;

class MenuInjectorSubscriber implements EventSubscriberInterface
{
    public function __construct(protected Environment $twig, private MenuBuilder $menuBuilder)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::REQUEST => 'onKernelRequest'];
    }

    public function onKernelRequest(RequestEvent $event)
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $menus = $this->menuBuilder->getMenus($event->getRequest()->getLocale());

        $this->twig->addGlobal('menus', $menus);
    }
}
