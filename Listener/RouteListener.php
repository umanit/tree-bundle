<?php

namespace Umanit\Bundle\TreeBundle\Listener;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Umanit\Bundle\TreeBundle\Entity\Node;
use Umanit\Bundle\TreeBundle\Model\TreeNodeInterface;

class RouteListener
{
    /**
     * @var array Configuration, which controller to call by class
     */
    protected $nodeTypes;

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
     * @param Registry $doctrine      Doctrine service
     * @param array    $nodeTypes     Configuration, which controller to call by class
     * @param string   $defaultLocale Default locale
     */
    public function __construct(
        Registry $doctrine,
        array $nodeTypes,
        $defaultLocale
    ) {
        $this->doctrine      = $doctrine;
        $this->nodeTypes     = $nodeTypes;
        $this->defaultLocale = $defaultLocale;
    }

    /**
     * Kernel controller event.
     *
     * Look for a path in database that match the route, get the object related to
     * this one and returns it (if needed)
     *
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $path       = $event->getRequest()->getPathInfo();
        $locale     = $event->getRequest()->getLocale();
        $repository = $this->doctrine->getRepository('UmanitTreeBundle:Node');

        // Root node
        if ($path === '/') {
            $node = $repository->getByPath(TreeNodeInterface::ROOT_NODE_PATH, $locale);
        } else {
            // Search for an other node
            $node = $repository->getByPath($path, $locale);
        }

        if ($node) {
            // Search for the entity related to the node
            $repo   = $this->doctrine->getRepository($node->getClassName());
            $entity = $repo->find($node->getClassId());

            if ($entity) {
                foreach ($this->nodeTypes as $nodeType) {
                    if ($entity instanceof $nodeType['class']) {
                        // Set a new controller and the object in 'contentObject'
                        $event->getRequest()->attributes->set('contentObject', $entity);
                        $event->getRequest()->attributes->set('contentNode', $node);
                        $event->getRequest()->attributes->set('_controller', $nodeType['controller']);
                        $event->getRequest()->attributes->set('template', $nodeType['template']);

                        return;
                    }
                }
            }
        }
    }
}
