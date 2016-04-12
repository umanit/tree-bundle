<?php

namespace Umanit\Bundle\TreeBundle\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

use Umanit\Bundle\TreeBundle\Entity\Node;
use Umanit\Bundle\TreeBundle\Model\TreeNodeInterface;
use Umanit\Bundle\TreeBundle\Model\TranslationNodeInterface;

class RouteListener
{
    /**
     * @var array Configuration, which controller to call by class
     */
    protected $controllersByClass;

    /**
     * @var string Default locale
     */
    protected $defaultLocale;

    /**
     * @var Registry
     */
    protected $doctrine;

    /**
     * Constructor.
     *
     * @param Registry $doctrine            Doctrine service
     * @param array    $controllersByClass  Configuration, which controller to call by class
     * @param string   $defaultLocale       Default locale
     */
    public function __construct(
        Registry $doctrine,
        array $controllersByClass,
        $defaultLocale
    ) {
        $this->doctrine = $doctrine;
        $this->controllersByClass = $controllersByClass;
        $this->defaultLocale = $defaultLocale;
    }

    /**
     * Kernel controller event
     *
     * Look for a path in database that match the route, get the object related to
     * this one and returns it (if needed)
     *
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $path = $event->getRequest()->getPathInfo();
        $locale = $event->getRequest()->getLocale();
        $repository = $this->doctrine->getRepository('UmanitTreeBundle:Node');

        // Root node
        if ($path === '/') {
            $node = $repository->findOneBy(array(
                'path'   => TreeNodeInterface::ROOT_NODE_PATH
            ));
        } else {
            // Search for a node in the current locale
            $node = $repository->findOneBy(array(
                'path'   => $path . '/',
                'locale' => $locale
            ));
        }

        // Redirect to the node in the default locale if node not found
        if (!$node && ($locale !== $this->defaultLocale)) {
            $node = $repository->findOneBy(array(
                'path'   => $path . '/',
                'locale' => $this->defaultLocale
            ));
        }

        if ($node) {
            // Search for the entity related to the node
            $repo = $this->doctrine->getRepository($node->getClassName());
            $entity = $repo->findOneById($node->getClassId());

            if ($entity) {
                foreach ($this->controllersByClass as $controller) {
                    if ($entity instanceof $controller['class']) {
                        // Set a new controller and the object in 'contentObject'
                        $event->getRequest()->attributes->set('contentObject', $entity);
                        $event->getRequest()->attributes->set('contentNode', $node);
                        $event->getRequest()->attributes->set('_controller', $controller['controller']);

                        return;
                    }
                }
            }
        }
    }
}
