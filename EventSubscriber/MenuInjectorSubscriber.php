<?php

namespace Umanit\Bundle\TreeBundle\EventSubscriber;

use AppBundle\Entity\Page;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig_Environment;
use Umanit\Bundle\TreeBundle\Entity\Menu;

class MenuInjectorSubscriber implements EventSubscriberInterface
{
    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var Twig_Environment $twig
     */
    protected $twig;

    public function __construct(EntityManagerInterface $entityManager, Twig_Environment $twig)
    {
        $this->em = $entityManager;
        $this->twig = $twig;
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

        $menuFlat = $this->em->getRepository(Menu::class)->getFrontMenu($event->getRequest()->getLocale());
        $menu = [];
        $parentId = [];
        $currentMenu = reset($menuFlat);
        if (!empty($currentMenu)) {
            do {
                $this->em->detach($currentMenu);

                $currentMenu->setChildren(new ArrayCollection());

                if (!empty($parentId) && $currentMenu->getParentId() != end($parentId)) {
                    do {
                        $parent = array_pop($menu);

                        if ($parent->getParentId() == null) {
                            array_push($menu, $parent);
                            break;
                        }
                        $grandParent = array_pop($menu);
                        $grandParent->addChildren($parent);
                        array_push($menu, $grandParent);
                        array_pop($parentId);

                    } while ($currentMenu->getParentId() != end($parentId) && end($parentId) !== false);

                    array_push($parentId, $currentMenu->getId());

                } else {
                    array_push($parentId, $currentMenu->getId());
                }

                array_push($menu, $currentMenu);

                $currentMenu = next($menuFlat);
            } while (!empty($currentMenu));
        }
        do {
            $parent = array_pop($menu);
            if (empty($parent)) {
                break;
            }
            if ($parent->getParentId() == null) {
                array_push($menu, $parent);
                break;
            }
            $grandParent = array_pop($menu);
            $grandParent->addChildren($parent);
            array_push($menu, $grandParent);
            array_pop($parentId);

        } while (end($parentId) !== false);

        $this->twig->addGlobal('menus', $menu);

        $this->injectSiteMap($event);

        $this->injectSearchAssistante($event);
    }

    protected function injectSiteMap(GetResponseEvent $event)
    {
        $this->twig->addGlobal(
            'sitemap',
            $this->em->getRepository(Page::class)->getSiteMap($event->getRequest()->getLocale())
        );
    }

    protected function injectSearchAssistante(GetResponseEvent $event)
    {
        // Récuparation du bouton assitant de recherche
        $searchAssistancePage = null;

        $configuration = $this->em->getRepository('Umanit\Bundle\TreeBundle:Configuration')->getConfiguration($event->getRequest()->getLocale());
        if (!empty($configuration) && !empty($configuration->getAssistanceLink())) {
            $link = $configuration->getAssistanceLink();

            if (!empty($link->getInternalLink())) {
                $internalLink = explode(';', $link->getInternalLink());
                $searchAssistancePage = $this->em->getRepository($internalLink[1])->find($internalLink[0]);
            }
        }
        $this->twig->addGlobal('search_assistante_page', $searchAssistancePage);
    }


}
