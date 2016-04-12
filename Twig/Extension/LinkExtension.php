<?php

namespace Umanit\Bundle\TreeBundle\Twig\Extension;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Umanit\Bundle\TreeBundle\Entity\Link;
use Umanit\Bundle\TreeBundle\Entity\Node;
use Umanit\Bundle\TreeBundle\Router\NodeRouter;

class LinkExtension extends \Twig_Extension
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
     * Constuctor
     * @param Registry $doctrine
     * @param NodeRouter $router
     */
    public function __construct(Registry $doctrine, NodeRouter $router)
    {
        $this->doctrine = $doctrine;
        $this->router   = $router;
    }

    /**
     * {@inheritDoc}
     */
    public function getFunctions()
    {
        return array(
            'get_path_from_link' => new \Twig_Function_Method($this, 'getPathLink'),
            'is_external_link'   => new \Twig_Function_Method($this, 'isExternalLink'),
            'get_path_from_node' => new \Twig_Function_method($this, 'getNodePath'),
            'get_path'           => new \Twig_Function_method($this, 'getPath'),
            'clear_path_cache'   => new \Twig_Function_method($this, 'clearCache')
        );
    }

    /**
     * Returns path for the given Link
     * @param Link $link
     * @return string
     */
    public function getPathLink(Link $link)
    {
        if ($link->getExternalLink()) {
            return $link->getExternalLink();
        }

        list($classId, $className) = explode(';', $link->getInternalLink());

        return $this->router->getPathClass($className, $classId);
    }

    /**
     * Check if the link is external.
     * @param Link $link
     * @return boolean
     */
    public function isExternalLink(Link $link)
    {
        return $link->getExternalLink() ? true : false;
    }

    /**
     * Returns the path of the given node
     * @param Node $node
     * @return string
     */
    public function getNodePath(Node $node)
    {
        return $this->router->getPathByNode($node);
    }

    /**
     * Get path for the given object (proxy to the service)
     * @param mixed $object       Entity searched
     * @param mixed $parentObject Object parent from which we want to get the nodes
     * @param bool  $root         Use root node as reference
     * @param bool  $absolute     URL absolue ou non
     * @return string
     */
    public function getPath($object, $parentObject = null, $root = false, $absolute = false)
    {
        return $this->router->getPath($object, $parentObject, $root, $absolute);
    }

    /**
     * Clear router's paths cache
     */
    public function clearCache()
    {
        $this->router->clearCache();
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'umanit_tree_link';
    }
}
