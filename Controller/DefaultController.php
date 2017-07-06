<?php

namespace Umanit\Bundle\TreeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

class DefaultController extends Controller
{
    public function notFoundAction(Request $request)
    {
        // Redirect 301 ?
        if ($this->getParameter('umanit_tree.seo.redirect_301')) {
            $path       = $request->getPathInfo();
            $locale     = $request->getLocale();
            $repository = $this->get('doctrine')->getRepository('UmanitTreeBundle:NodeHistory');

            $node = $repository->getByPath($path, $locale);

            if ($node) {
                // Search for the entity related to the node
                $repo   = $this->get('doctrine')->getRepository($node->getClassName());
                $entity = $repo->findOneById($node->getClassId());

                if ($entity) {
                    $url = $this->get('umanit.tree.router')->getPath($entity);

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
