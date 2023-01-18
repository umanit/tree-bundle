<?php

namespace Umanit\TreeBundle\Twig\Extension;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Umanit\TreeBundle\Entity\Node;
use Umanit\TreeBundle\Model\TreeNodeInterface;
use Umanit\TreeBundle\Router\NodeRouter;

class BreadcrumbExtension extends AbstractExtension
{
    public function __construct(
        protected RequestStack $requestStack,
        protected Translator $translator,
        protected array $configuration,
        protected NodeRouter $router,
        protected Registry $doctrine
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('get_breadcrumb', [$this, 'getBreadcrumb']),
        ];
    }

    /**
     * Returns an array with the breadcrumb of the current page
     *
     * @param array $defaultElements Default elements in the breadcrumb
     */
    public function getBreadcrumb(array $defaultElements = []): array
    {
        $request = $this->requestStack->getCurrentRequest();
        $node = $request->attributes->get('contentNode', null);

        $breadcrumb = [];

        // If homepage or unknown page
        if (!is_null($node) && $node->getPath() !== TreeNodeInterface::ROOT_NODE_PATH && empty($defaultElements)) {
            do {
                $repository = $this->doctrine->getRepository($node->getClassName());

                if (!$entity = $repository->findOneBy(['id' => $node->getClassId()])) {
                    break;
                }

                $element = [
                    'name' => $entity->__toString(),
                    'link' => $this->router->getPathByNode($node),
                ];

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
     */
    private function getRootNode(): array
    {
        $repository = $this->doctrine->getRepository(Node::class);

        $defaultNode = $repository->findOneBy([
            'path' => TreeNodeInterface::ROOT_NODE_PATH,
        ]);

        return [
            'name' => $this->translator->trans(
                $this->configuration['root_name'],
                [],
                $this->configuration['translation_domain']
            ),
            'link' => $defaultNode ? $this->router->getPathByNode($defaultNode) : '#',
        ];
    }
}
