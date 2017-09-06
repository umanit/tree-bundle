<?php

namespace Umanit\Bundle\TreeBundle\Router;

use Umanit\Bundle\TreeBundle\Entity\Node;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Umanit\Bundle\TreeBundle\Model\TreeNodeInterface;

/**
 * Router that returns a path for the given node.
 */
class NodeRouter
{
    /**
     * @var Registry
     */
    protected $doctrine;

    /**
     * @var Router Symfony2 router
     */
    protected $router;

    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @var array Cache to avoid an huge amount of requests
     */
    protected $cache;

    /**
     * Constructor.
     *
     * @param Registry        $doctrine     Doctrine ORM
     * @param RouterInterface $router       Symfony2 router
     * @param RequestStack    $requestStack Current request
     */
    public function __construct(Registry $doctrine, RouterInterface $router, RequestStack $requestStack)
    {
        $this->doctrine     = $doctrine;
        $this->router       = $router;
        $this->requestStack = $requestStack;
    }

    /**
     * Get path for the given object.
     *
     * @param mixed $object       Entity searched
     * @param mixed $parentObject Object parent from which we want to get the nodes
     * @param bool  $root         Use root node as reference
     * @param bool  $absolute     Absolute URL or not
     * @param bool  $locale       Locale to use
     * @param array $parameters   URL parameters
     *
     * @return string
     */
    public function getPath(
        $object,
        $parentObject = null,
        $root = false,
        $absolute = false,
        $locale = null,
        $parameters = []
    ) {
        return $object ? $this->getPathClass(
            $this->doctrine->getManager()->getClassMetadata(get_class($object))->getName(),
            $object->getId(),
            $parentObject,
            $root,
            $absolute,
            $locale,
            $parameters
        ) : '#';
    }

    /**
     * Returns a path for the given matching the given className and classId.
     *
     * @param string $className    Class full name (with namespace)
     * @param int    $classId      Instance ID
     * @param mixed  $parentObject Object parent from which we want to get the nodes
     * @param bool   $root         Use root node as reference
     * @param bool   $absolute     Absolute URL or not
     * @param bool   $locale       Locale to use
     * @param array  $parameters   URL parameters
     *
     * @return string
     */
    public function getPathClass(
        $className,
        $classId,
        $parentObject = null,
        $root = false,
        $absolute = false,
        $locale = null,
        $parameters = []
    ) {
        $referenceNode = $this->requestStack->getCurrentRequest()->attributes->get('contentNode', null);
        if ($referenceNode === null || $referenceNode->getPath() === TreeNodeInterface::ROOT_NODE_PATH) {
            $referenceNode = null;
        }

        if (!is_null($parentObject) && $root === false) {
            $classNameParent = $this->doctrine->getManager()->getClassMetadata(get_class($parentObject))->getName();
            if ($classNameParent === "Umanit\Bundle\TreeBundle\Entity\Node") {
                $referenceNode = $parentObject;
            } else {
                $referenceNode = $this->buildNode(
                    $classNameParent,
                    $parentObject->getId(),
                    null,
                    false,
                    $locale
                );
            }
        }

        $node = $this->buildNode($className, $classId, $referenceNode, $root, $locale);

        return is_null($node) ? '#' : $this->getPathByNode($node, $absolute, $parameters);
    }

    /**
     * Builds path below the following referenceNode.
     *
     * @param string    $className     Class name
     * @param int       $classId       Class identifier
     * @param Node|null $referenceNode Node reference
     * @param bool      $root          Use root node as reference
     * @param bool      $locale        Locale to use
     *
     * @return string
     */
    public function buildNode($className, $classId, $referenceNode, $root, $locale)
    {
        $manager = $this->doctrine->getRepository('Umanit\Bundle\TreeBundle\Entity\Node');

        $parents = [];
        if ($referenceNode && !$root) {
            do {
                $parents[] = $referenceNode->getId();
            } while ($referenceNode = $referenceNode->getParent());
        }

        $node = $manager->searchNode(
            $className,
            $classId,
            $parents,
            $locale ? $locale : $this->requestStack->getCurrentRequest()->getLocale()
        );

        return $node;
    }

    /**
     * Clear node's cache.
     */
    public function clearCache()
    {
        $this->cache = array();
    }

    /**
     * Returns the relative path to access the given node.
     *
     * @param Node  $node
     * @param bool  $absolute   Absolute URL or not
     * @param array $parameters URL parameters
     *
     * @return string
     */
    public function getPathByNode(Node $node, $absolute = false, $parameters = [])
    {
        // Root page
        if ($node->getPath() === TreeNodeInterface::ROOT_NODE_PATH) {
            return $this->router->generate('umanit.tree.default', array_merge(array(
                'path' => '',
            ), $parameters));
        }

        return $this->router->generate('umanit.tree.default', array_merge(array(
            'path' => substr($node->getPath(), 1),
        ), $parameters), $absolute);
    }
}
