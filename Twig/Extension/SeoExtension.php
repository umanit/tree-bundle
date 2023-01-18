<?php

namespace Umanit\TreeBundle\Twig\Extension;

use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Umanit\TreeBundle\Helper\Excerpt;
use Umanit\TreeBundle\Helper\Title;
use Umanit\TreeBundle\Model\SeoInterface;

class SeoExtension extends AbstractExtension
{
    protected ?Request $request;

    /**
     * @param RequestStack $request       Current request
     * @param Translator   $translator    Translation service
     * @param Excerpt      $excerpt       Excerpt helper service
     * @param Title        $title
     * @param array        $configuration SEO configuration from umanit_tree key
     */
    public function __construct(
        RequestStack $request,
        protected Translator $translator,
        private Excerpt $excerpt,
        private Title $title,
        protected array $configuration
    ) {
        $this->request = $request->getCurrentRequest();
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('get_seo_title', [$this, 'getSeoTitle']),
            new TwigFunction('get_seo_description', [$this, 'getSeoDescription']),
            new TwigFunction('get_seo_keywords', [$this, 'getSeoKeywords']),
        ];
    }

    /**
     * Returns SEO title of the page. Puts the default value if not given.
     *
     * @param string $default  Default title for the page
     * @param bool   $override Set default value if override is set to true
     */
    public function getSeoTitle($default = '', $override = false): string
    {
        if ($default && $override) {
            return $default;
        }

        $defaultTitle = $default ?: $this->translator->trans(
            $this->configuration['default_title'],
            [],
            $this->configuration['translation_domain']
        );

        if (!empty($this->request->attributes) && $contentObject = $this->request->attributes->get(
                'contentObject',
                null
            )) {
            if ($contentObject instanceof SeoInterface) {
                return ($contentObject->getSeoTitle() ?: $this->title->fromEntity($contentObject)).' | '.$defaultTitle;
            }
        }

        return $defaultTitle;
    }

    /**
     * Returns SEO description of the page. Puts the default value if not given.
     *
     * @param string $default  Default title for the page
     * @param int    $override Set default value if override is set to true
     *
     * @throws \ReflectionException
     */
    public function getSeoDescription($default = '', $override = false): string
    {
        if ($default && $override) {
            return $default;
        }

        if (!empty($this->request->attributes) && $contentObject = $this->request->attributes->get(
                'contentObject',
                null
            )) {
            if ($contentObject instanceof SeoInterface) {
                return $contentObject->getSeoDescription() ?: $this->excerpt->fromEntity($contentObject);
            }
        }

        return $default ?: $this->translator->trans(
            $this->configuration['default_description'],
            [],
            $this->configuration['translation_domain']
        );
    }

    /**
     * Returns SEO keywords of the page. Puts the default value if not given.
     *
     * @param string $default  Default title for the page
     * @param int    $override Set default value if override is set to true
     */
    public function getSeoKeywords($default = '', $override = false): string
    {
        if ($default && $override) {
            return $default;
        }

        if (!empty($this->request->attributes) && $contentObject = $this->request->attributes->get(
                'contentObject',
                null
            )) {
            if ($contentObject instanceof SeoInterface && !empty($contentObject->getSeoKeywords())) {
                return $contentObject->getSeoKeywords();
            }
        }

        return $default ?: $this->translator->trans(
            $this->configuration['default_keywords'],
            [],
            $this->configuration['translation_domain']
        );
    }
}
