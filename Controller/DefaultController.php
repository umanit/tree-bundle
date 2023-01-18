<?php

namespace Umanit\TreeBundle\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Umanit\TreeBundle\Entity\NodeHistory;
use Umanit\TreeBundle\Router\NodeRouter;

class DefaultController extends AbstractController
{
    private ManagerRegistry $registry;

    private NodeRouter $router;

    public function __construct(
        ManagerRegistry $registry,
        NodeRouter $router
    ) {
        $this->registry = $registry;
        $this->router = $router;
    }

    public function notFound(Request $request, string $path): RedirectResponse
    {
        // Redirect 301 ?
        if ($this->getParameter('umanit_tree.seo.redirect_301')) {
            $path = $request->getPathInfo();
            $locale = $request->getLocale();
            $repository = $this->registry->getRepository(NodeHistory::class);

            $node = $repository->getByPath($path, $locale);

            if ($node) {
                // Search for the entity related to the node
                $repo = $this->registry->getRepository($node->getClassName());
                $entity = $repo->findOneBy(['id' => $node->getClassId()]);

                if ($entity) {
                    $url = $this->router->getPath($entity);

                    if ($request->getRequestUri() != $url) {
                        return new RedirectResponse(
                            $url,
                            301
                        );
                    }
                }
            }
        }

        throw $this->createNotFoundException('Page not found');
    }
}
