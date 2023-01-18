<?php

namespace Umanit\TreeBundle\Listener;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Umanit\TreeBundle\Entity\Node;
use Umanit\TreeBundle\Model\TreeNodeInterface;

class RouteListener
{
    public function __construct(
        protected Registry $doctrine,
        protected array $nodeTypes,
        protected string $defaultLocale
    ) {
    }

    /**
     * Kernel controller event.
     *
     * Look for a path in database that match the route, get the object related to
     * this one and returns it (if needed)
     *
     * @param RequestEvent $event
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        $path = $event->getRequest()->getPathInfo();
        $locale = $event->getRequest()->getLocale();
        $repository = $this->doctrine->getRepository(Node::class);

        // Root node
        if ($path === '/' || $path === '') {
            $node = $repository->getByPath(TreeNodeInterface::ROOT_NODE_PATH, $locale);
        } else {
            // Search for an other node
            $node = $repository->getByPath($path, $locale);
        }

        if ($node) {
            // Search for the entity related to the node
            $repo = $this->doctrine->getRepository($node->getClassName());
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
