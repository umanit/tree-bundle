<?php

namespace Umanit\Bundle\TreeBundle\EventSubscriber;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
class SonataMenuBuilderSubscriber implements EventSubscriberInterface
{
    /**
     * @var
     */
    private $menuEntityClass;
    /**
     * @var AuthorizationCheckerInterface
     */
    private $checker;

    /**
     * SonataMenuBuilderSubscriber constructor.
     *
     * @param string                        $menuEntityClass
     * @param AuthorizationCheckerInterface $checker
     */
    public function __construct($menuEntityClass, AuthorizationCheckerInterface $checker)
    {
        $this->menuEntityClass = $menuEntityClass;
        $this->checker         = $checker;
    }

    public static function getSubscribedEvents()
    {
        return ['sonata.admin.event.configure.menu.sidebar' => 'addMenuItems'];
    }

    public function addMenuItems(Event $event)
    {
        if ($this->checker->isGranted('ROLE_TREE_MENU_ADMIN') &&
            !empty($this->menuEntityClass) &&
            method_exists($event, 'getMenu') &&
            get_class($event->getMenu()) === 'Knp\Menu\MenuItem'
        ) {

            /** @var \Knp\Menu\ItemInterface $menu */
            $menu = $event->getMenu();

            $menu->addChild('Menu', ['route' => 'tree_admin_menu_dashboard', 'extras' => ['icon' => '<i class="fa fa-bars"></i>']]);
        }
    }
}
