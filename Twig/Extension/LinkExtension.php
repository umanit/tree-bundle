<?php

namespace Umanit\Bundle\TreeBundle\Twig\Extension;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\RouterInterface;
use Umanit\Bundle\TreeBundle\Entity\Link;
use Umanit\Bundle\TreeBundle\Entity\Node;
use Umanit\Bundle\TreeBundle\Router\NodeRouter;

class LinkExtension extends \Twig_Extension
{
    /**
     * @var RouterInterface
     */
    protected $router;
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * Constuctor.
     *
     * @param NodeRouter             $router
     * @param EntityManagerInterface $em
     */
    public function __construct(NodeRouter $router, EntityManagerInterface $em)
    {
        $this->router = $router;
        $this->em     = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            new \Twig_Function('get_path_from_link', [$this, 'getPathLink']),
            new \Twig_Function('is_external_link', [$this, 'isExternalLink']),
            new \Twig_Function('get_path_from_node', [$this, 'getNodePath']),
            new \Twig_Function('get_path', [$this, 'getPath']),
            new \Twig_Function('clear_path_cache', [$this, 'clearCache']),
            new \Twig_Function('get_object_from_link', [$this, 'getObjectFromLink']),
        );
    }

    /**
     * Returns path for the given Link.
     *
     * @param Link $link
     *
     * @return string
     */
    public function getPathLink(Link $link)
    {
        return $this->router->getPathFromLink($link);
    }

    /**
     * Check if the link is external.
     *
     * @param Link $link
     *
     * @return bool
     */
    public function isExternalLink($link)
    {
        return !empty($link) && $link->getExternalLink() ? true : false;
    }

    /**
     * Returns the path of the given node.
     *
     * @param Node  $node
     * @param bool  $absolute   Absolute URL
     * @param array $parameters URL parameters
     *
     * @return string
     */
    public function getNodePath(Node $node, $absolute = false, $parameters = [])
    {
        return $this->router->getPathByNode($node, $absolute, $parameters);
    }

    /**
     * Get path for the given object (proxy to the service).
     *
     * @param mixed $object       Entity searched
     * @param mixed $parentObject Object parent from which we want to get the nodes
     * @param bool  $root         Use root node as reference
     * @param bool  $absolute     Absolute URL
     * @param array $parameters   URL parameters
     *
     * @return string
     */
    public function getPath($object, $parentObject = null, $root = false, $absolute = false, $parameters = [])
    {
        return $this->router->getPath($object, $parentObject, $root, $absolute, null, $parameters);
    }

    /**
     * Get the object associated to an internal Link.
     *
     * @param Link $link
     *
     * @return null|object
     */
    public function getObjectFromLink(Link $link)
    {
        if ($this->isExternalLink($link)) {
            return null;
        }

        list($objectId, $class) = explode(';', $link->getInternalLink());

        return $this->em->getRepository($class)->find($objectId);
    }

    /**
     * Clear router's paths cache.
     */
    public function clearCache()
    {
        $this->router->clearCache();
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'umanit_tree_link';
    }
}
