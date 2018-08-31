<?php

namespace Umanit\Bundle\TreeBundle\Router;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Umanit\Bundle\TreeBundle\Entity\Link;
use Umanit\Bundle\TreeBundle\Entity\Node;
use Umanit\Bundle\TreeBundle\Helper\NodeHelper;
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
     * @param NodeHelper      $nodeHelper
     */
    public function __construct(Registry $doctrine, RouterInterface $router, RequestStack $requestStack, NodeHelper $nodeHelper)
    {
        $this->doctrine     = $doctrine;
        $this->router       = $router;
        $this->requestStack = $requestStack;
        $this->nodeHelper   = $nodeHelper;
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
        $referenceNode = null;

        // In the app/console context, the request is an empty object request
        if ($this->requestStack->getCurrentRequest() !== null) {
            $referenceNode = $this->requestStack->getCurrentRequest()->attributes->get('contentNode', null);
            $locale = $locale ?? $this->requestStack->getCurrentRequest()->getLocale();
        }
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
            $locale
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
        $entity = $this->nodeHelper->getAssociatedEntity($node);

        if ($entity instanceof PathByNodeInterface) {
            return $entity->getPathByNode($absolute, $parameters);
        }

        $parameters['_locale'] = $node->getLocale();

        // Root page
        if ($node->getPath() === TreeNodeInterface::ROOT_NODE_PATH) {
            return $this->router->generate('umanit.tree.default', array_merge([
                'path' => '',
            ], $parameters), $absolute ? UrlGeneratorInterface::ABSOLUTE_URL : UrlGeneratorInterface::ABSOLUTE_PATH);
        }

        return $this->router->generate('umanit.tree.default', array_merge([
            'path' => substr($node->getPath(), 1),
        ], $parameters), $absolute ? UrlGeneratorInterface::ABSOLUTE_URL : UrlGeneratorInterface::ABSOLUTE_PATH);
    }

    /**
     * Returns path for the given Link.
     *
     * @param Link $link
     *
     * @return string
     */
    public function getPathFromLink(Link $link)
    {
        if ($link->getExternalLink()) {
            return $link->getExternalLink();
        }

        if ($link->getInternalLink()) {
            list($classId, $className) = explode(';', $link->getInternalLink());

            return $this->getPathClass($className, $classId);
        }

        return '#';
    }
}
