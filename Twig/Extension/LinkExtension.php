<?php

namespace Umanit\TreeBundle\Twig\Extension;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\RouterInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Umanit\TreeBundle\Entity\Link;
use Umanit\TreeBundle\Entity\Node;
use Umanit\TreeBundle\Router\NodeRouter;

class LinkExtension extends AbstractExtension
{
    public function __construct(protected NodeRouter $router, private EntityManagerInterface $em)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('get_path_from_link', [$this, 'getPathLink']),
            new TwigFunction('is_external_link', [$this, 'isExternalLink']),
            new TwigFunction('get_path_from_node', [$this, 'getNodePath']),
            new TwigFunction('get_path', [$this, 'getPath']),
            new TwigFunction('clear_path_cache', [$this, 'clearCache']),
            new TwigFunction('get_object_from_link', [$this, 'getObjectFromLink']),
        ];
    }

    /**
     * Returns path for the given Link.
     *
     * @param Link $link
     *
     * @return string
     */
    public function getPathLink(Link $link): string
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
    public function isExternalLink($link): bool
    {
        return !empty($link) && $link->getExternalLink();
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
    public function getNodePath(Node $node, $absolute = false, $parameters = []): string
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
     */
    public function getPath(
        mixed $object,
        mixed $parentObject = null,
        $root = false,
        $absolute = false,
        $parameters = []
    ): string {
        $locale = null;
        if (method_exists($object, 'getLocale')) {
            $locale = $object->getLocale();
        }

        return $this->router->getPath($object, $parentObject, $root, $absolute, $locale, $parameters);
    }

    /**
     * Get the object associated to an internal Link.
     *
     * @param Link $link
     *
     * @return null|object
     */
    public function getObjectFromLink(Link $link): ?object
    {
        if ($this->isExternalLink($link)) {
            return null;
        }

        [$objectId, $class] = explode(';', $link->getInternalLink());

        return $this->em->getRepository($class)->find($objectId);
    }

    /**
     * Clear router's paths cache.
     */
    public function clearCache()
    {
        $this->router->clearCache();
    }
}
