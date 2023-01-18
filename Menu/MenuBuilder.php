<?php

namespace Umanit\TreeBundle\Menu;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;

class MenuBuilder
{
    public function __construct(private EntityManagerInterface $em, private string $menuEntityClass)
    {
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
     *
     */
    public function getMenus(string $locale): array
    {
        return $this->buildMenus($locale);
    }

    /**
     * Builds the menus for the given locale.
     *
     *
     */
    protected function buildMenus(string $locale): array
    {
        $menusFlat = $this->em->getRepository($this->menuEntityClass)->getFrontMenu($locale);
        $menus = [];
        $parentId = [];
        $currentMenu = reset($menusFlat);

        if (!empty($currentMenu)) {
            do {
                $this->em->detach($currentMenu);

                $currentMenu->setChildren(new ArrayCollection());

                if (!empty($parentId) && $currentMenu->getParentId() !== end($parentId)) {
                    do {
                        $parent = array_pop($menus);

                        if ($parent->getParentId() == null) {
                            $menus[] = $parent;
                            break;
                        }

                        $grandParent = array_pop($menus);
                        $grandParent->addChildren($parent);
                        $menus[] = $grandParent;

                        array_pop($parentId);
                    } while ($currentMenu->getParentId() !== end($parentId) && end($parentId) !== false);
                }

                $parentId[] = $currentMenu->getId();
                $menus[] = $currentMenu;

                $currentMenu = next($menusFlat);
            } while (!empty($currentMenu));
        }

        do {
            $parent = array_pop($menus);
            if (empty($parent)) {
                break;
            }
            if ($parent->getParentId() === null) {
                $menus[] = $parent;
                break;
            }
            $grandParent = array_pop($menus);
            $grandParent->addChildren($parent);
            $menus[] = $grandParent;

            array_pop($parentId);
        } while (end($parentId) !== false);

        return $menus;
    }
}
