<?php

namespace Umanit\TreeBundle\Router;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Umanit\TreeBundle\Entity\Link;
use Umanit\TreeBundle\Entity\Node;
use Umanit\TreeBundle\Model\TreeNodeInterface;

/**
 * Router that returns a path for the given node.
 */
class NodeRouter
{
    /**
     * @var array Cache to avoid a huge amount of requests
     */
    protected array $cache;

    public function __construct(
        protected Registry $doctrine,
        protected RouterInterface $router,
        protected RequestStack $requestStack
    ) {
    }

    /**
     * Get path for the given object.
     *
     * @param mixed       $object       Entity searched
     * @param mixed       $parentObject Object parent from which we want to get the nodes
     * @param bool        $root         Use root node as reference
     * @param bool        $absolute     Absolute URL or not
     * @param string|null $locale       Locale to use
     * @param array       $parameters   URL parameters
     */
    public function getPath(
        mixed $object,
        mixed $parentObject = null,
        bool $root = false,
        bool $absolute = false,
        ?string $locale = null,
        array $parameters = []
    ): string {
        return $object ? $this->getPathClass(
            $this->doctrine->getManager()->getClassMetadata($object::class)->getName(),
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
     * @param string      $className    Class full name (with namespace)
     * @param int         $classId      Instance ID
     * @param mixed       $parentObject Object parent from which we want to get the nodes
     * @param bool        $root         Use root node as reference
     * @param bool        $absolute     Absolute URL or not
     * @param string|null $locale       Locale to use
     * @param array       $parameters   URL parameters
     */
    public function getPathClass(
        string $className,
        int $classId,
        mixed $parentObject = null,
        bool $root = false,
        bool $absolute = false,
        ?string $locale = null,
        array $parameters = []
    ): string {
        $referenceNode = null;

        // In the app/console context, the request is an empty object request
        if ($this->requestStack->getCurrentRequest() !== null) {
            $referenceNode = $this->requestStack->getCurrentRequest()->attributes->get('contentNode', null);
            $locale ??= $this->requestStack->getCurrentRequest()->getLocale();
        }

        if ($referenceNode === null || $referenceNode->getPath() === TreeNodeInterface::ROOT_NODE_PATH) {
            $referenceNode = null;
        }

        if (!is_null($parentObject) && $root === false) {
            $classNameParent = $this->doctrine->getManager()->getClassMetadata($parentObject::class)->getName();

            if ($classNameParent === Node::class) {
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
     * @param string    $locale        Locale to use
     */
    public function buildNode(string $className, int $classId, ?Node $referenceNode, bool $root, string $locale): ?Node
    {
        $manager = $this->doctrine->getRepository(Node::class);

        $parents = [];
        if ($referenceNode && !$root) {
            do {
                $parents[] = $referenceNode->getId();
            } while ($referenceNode = $referenceNode->getParent());
        }

        return $manager->searchNode(
            $className,
            $classId,
            $parents,
            $locale
        );
    }

    /**
     * Clear node's cache.
     */
    public function clearCache(): void
    {
        $this->cache = [];
    }

    /**
     * Returns the relative path to access the given node.
     *
     * @param bool  $absolute   Absolute URL or not
     * @param array $parameters URL parameters
     *
     */
    public function getPathByNode(Node $node, bool $absolute = false, array $parameters = []): string
    {
        $parameters['_locale'] = $node->getLocale();

        // Root page
        if ($node->getPath() === TreeNodeInterface::ROOT_NODE_PATH) {
            return $this->router->generate(
                'umanit.tree.default',
                array_merge([
                    'path' => '',
                ], $parameters),
                $absolute ? UrlGeneratorInterface::ABSOLUTE_URL : UrlGeneratorInterface::ABSOLUTE_PATH
            );
        }

        return $this->router->generate(
            'umanit.tree.default',
            array_merge([
                'path' => substr($node->getPath(), 1),
            ], $parameters),
            $absolute ? UrlGeneratorInterface::ABSOLUTE_URL : UrlGeneratorInterface::ABSOLUTE_PATH
        );
    }

    /**
     * Returns path for the given Link.
     *
     * @param Link $link
     *
     * @return string
     */
    public function getPathFromLink(Link $link): string
    {
        if ($link->getExternalLink()) {
            return $link->getExternalLink();
        }

        if ($link->getInternalLink()) {
            [$classId, $className] = explode(';', $link->getInternalLink());

            return $this->getPathClass($className, $classId);
        }

        return '#';
    }
}
