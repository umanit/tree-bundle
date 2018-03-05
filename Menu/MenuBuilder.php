<?php

namespace Umanit\Bundle\TreeBundle\Menu;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
class MenuBuilder
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var string
     */
    private $menuEntityClass;

    /**
     * MenuBuilder constructor.
     *
     * @param EntityManagerInterface $em
     * @param string                 $menuEntityClass
     */
    public function __construct(EntityManagerInterface $em, $menuEntityClass)
    {
        $this->em              = $em;
        $this->menuEntityClass = $menuEntityClass;
    }

    public function getMenu($name, $locale)
    {
        $menus = $this->getMenus($locale);

        if (isset($menus[$name])) {
            return $menus[$name];
        }

        return [];
    }

    /**
     * Returns the menus for the given locale
     *
     * @param string $locale
     *
     * @return array
     */
    public function getMenus($locale)
    {
        return $this->buildMenus($locale);
    }

    /**
     * Builds the menus for the given locale.
     *
     * @param string $locale
     *
     * @return array
     */
    protected function buildMenus($locale)
    {
        $menusFlat   = $this->em->getRepository($this->menuEntityClass)->getFrontMenu($locale);
        $menus       = [];
        $parentId    = [];
        $currentMenu = reset($menusFlat);
        if (!empty($currentMenu)) {
            do {
                $this->em->detach($currentMenu);

                $currentMenu->setChildren(new ArrayCollection());

                if (!empty($parentId) && $currentMenu->getParentId() !== end($parentId)) {
                    do {
                        $parent = array_pop($menus);

                        if ($parent->getParentId() == null) {
                            array_push($menus, $parent);
                            break;
                        }
                        $grandParent = array_pop($menus);
                        $grandParent->addChildren($parent);
                        array_push($menus, $grandParent);
                        array_pop($parentId);

                    } while ($currentMenu->getParentId() !== end($parentId) && end($parentId) !== false);

                    array_push($parentId, $currentMenu->getId());

                } else {
                    array_push($parentId, $currentMenu->getId());
                }

                array_push($menus, $currentMenu);

                $currentMenu = next($menusFlat);
            } while (!empty($currentMenu));
        }
        do {
            $parent = array_pop($menus);
            if (empty($parent)) {
                break;
            }
            if ($parent->getParentId() === null) {
                array_push($menus, $parent);
                break;
            }
            $grandParent = array_pop($menus);
            $grandParent->addChildren($parent);
            array_push($menus, $grandParent);
            array_pop($parentId);

        } while (end($parentId) !== false);

        return $menus;
    }
}
