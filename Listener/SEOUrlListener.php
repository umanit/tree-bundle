<?php

namespace Umanit\Bundle\TreeBundle\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Umanit\Bundle\TreeBundle\Entity\Node;
use Umanit\Bundle\TreeBundle\Model\TreeNodeInterface;
use Umanit\Bundle\TreeBundle\Model\TranslationNodeInterface;
use Umanit\Bundle\TreeBundle\Router\NodeRouter;

class SEOUrlListener
{
    /**
     * @var Registry
     */
    protected $doctrine;

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * Constructor.
     *
     * @param Registry $doctrine            Doctrine service
     * @param NodeRouter $router
     */
    public function __construct(
        Registry $doctrine,
        NodeRouter $router
    ) {
        $this->doctrine = $doctrine;
        $this->router = $router;
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
        $repository = $this->doctrine->getRepository('UmanitTreeBundle:NodeHistory');

        $node = $repository->getByPath($path, $locale);

        if ($node) {
            // Search for the entity related to the node
            $repo = $this->doctrine->getRepository($node->getClassName());
            $entity = $repo->findOneById($node->getClassId());

            if ($entity) {
                $url = $this->router->getPath($entity);

                $request = $event->getRequest();

                if ($request->getPathInfo() != $url) {
                    $event->setResponse(
                        new RedirectResponse(
                            str_replace($request->getPathInfo(), $url, $request->getRequestUri()),
                            301
                        )
                    );
                    $event->stopPropagation();
                }
            }
        }
    }
}
