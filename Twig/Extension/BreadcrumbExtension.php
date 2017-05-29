<?php

namespace Umanit\Bundle\TreeBundle\Twig\Extension;

use Symfony\Component\HttpFoundation\RequestStack;
use Umanit\Bundle\TreeBundle\Model\SeoInterface;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Umanit\Bundle\TreeBundle\Entity\Node;
use Umanit\Bundle\TreeBundle\Router\NodeRouter;
use Umanit\Bundle\TreeBundle\Model\TreeNodeInterface;

class BreadcrumbExtension extends \Twig_Extension
{
    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @var array
     */
    protected $configuration;

    /**
     * @var Translator
     */
    protected $translator;

    /**
     * @var NodeRouter
     */
    protected $router;

    /**
     * @var Registry
     */
    protected $doctrine;

    /**
     * Constuctor
     * @param RequestStack $request       Current request
     * @param Translator   $translator    Translation service
     * @param array        $configuration SEO configuration from umanit_tree key
     * @param NodeRouter   $router        UmanitTreeBundle router
     * @param Registry     $doctrine      Doctrine ORM
     */
    public function __construct(
        RequestStack $request,
        Translator $translator,
        array $configuration,
        NodeRouter $router,
        Registry $doctrine
    ) {
        $this->requestStack  = $request;
        $this->configuration = $configuration;
        $this->translator    = $translator;
        $this->router        = $router;
        $this->doctrine      = $doctrine;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'umanit_tree_breadcrumb';
    }

    /**
     * {@inheritDoc}
     */
    public function getFunctions()
    {
        return array(
            new \Twig_Function('get_breadcrumb', 'getBreadcrumb')
        );
    }

    /**
     * Returns an array with the breadcrumb of the current page
     *
     * @param array $defaultElements Default elements in the breadcrumb
     *
     * @return array
     */
    public function getBreadcrumb(array $defaultElements = array())
    {
        $request = $this->requestStack->getCurrentRequest();
        $node = $request->attributes->get('contentNode', null);

        $breadcrumb  = array();

        // If homepage or unknown page
        if (!is_null($node) && $node->getPath() !== TreeNodeInterface::ROOT_NODE_PATH && empty($defaultElements)) {
            do {
                $repository = $this->doctrine->getRepository($node->getClassName());
                if (!$entity = $repository->findOneBy(array('id' => $node->getClassId()))) {
                    break;
                }

                $element = array(
                    'name' => $entity->__toString(),
                    'link' => $this->router->getPathByNode($node)
                );

                array_unshift($breadcrumb, $element);
            } while ($node = $node->getParent());
        } else {
            $breadcrumb = $defaultElements;
        }

        array_unshift($breadcrumb, $this->getRootNode());

        return $breadcrumb;
    }

    /**
     * Returns the root node of the website
     * @return array
     */
    private function getRootNode()
    {
        $repository = $this->doctrine->getRepository('UmanitTreeBundle:Node');

        $defaultNode = $repository->findOneBy(array(
            'path'   => TreeNodeInterface::ROOT_NODE_PATH
        ));

        return array(
            'name' => $this->translator->trans(
                $this->configuration['root_name'],
                array(),
                $this->configuration['translation_domain']
            ),
            'link' => $this->router->getPathByNode($defaultNode)
        );
    }
}
