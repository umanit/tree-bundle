<?php

namespace Umanit\TreeBundle\EventSubscriber;

use Knp\Menu\ItemInterface;
use Knp\Menu\MenuItem;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\EventDispatcher\Event;

class SonataMenuBuilderSubscriber implements EventSubscriberInterface
{
    public function __construct(private $menuEntityClass, private AuthorizationCheckerInterface $checker)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return ['sonata.admin.event.configure.menu.sidebar' => 'addMenuItems'];
    }

    public function addMenuItems(Event $event)
    {
        if ($this->checker->isGranted('ROLE_TREE_MENU_ADMIN') &&
            !empty($this->menuEntityClass) &&
            method_exists($event, 'getMenu') &&
            $event->getMenu()::class === MenuItem::class) {
            /** @var ItemInterface $menu */
            $menu = $event->getMenu();

            $menu->addChild('Menu', [
                'route'  => 'tree_admin_menu_dashboard',
                'extras' => ['icon' => '<i class="fas fa-bars"></i>'],
            ]);
        }
    }
}
