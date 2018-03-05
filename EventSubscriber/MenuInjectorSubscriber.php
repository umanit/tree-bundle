<?php

namespace Umanit\Bundle\TreeBundle\EventSubscriber;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig_Environment;
use Umanit\Bundle\TreeBundle\Menu\MenuBuilder;

class MenuInjectorSubscriber implements EventSubscriberInterface
{
    /**
     * @var Twig_Environment $twig
     */
    protected $twig;

    /**
     * @var MenuBuilder
     */
    private $menuBuilder;

    /**
     * MenuInjectorSubscriber constructor.
     *
     * @param Twig_Environment $twig
     * @param MenuBuilder      $menuBuilder
     */
    public function __construct(Twig_Environment $twig, MenuBuilder $menuBuilder)
    {
        $this->twig        = $twig;
        $this->menuBuilder = $menuBuilder;
    }

    public static function getSubscribedEvents()
    {
        return [KernelEvents::REQUEST => 'onKernelRequest'];
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $menus = $this->menuBuilder->getMenus($event->getRequest()->getLocale());

        $this->twig->addGlobal('menus', $menus);
    }
}
