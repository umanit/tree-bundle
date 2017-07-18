<?php

namespace Umanit\Bundle\TreeBundle\Twig\Extension;

use Symfony\Component\HttpFoundation\RequestStack;
use Umanit\Bundle\TreeBundle\Model\SeoInterface;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;

class SeoExtension extends \Twig_Extension
{
    /**
     * @var RequestStack
     */
    protected $request;

    /**
     * @var array
     */
    protected $configuration;

    /**
     * @var Translator
     */
    protected $translator;

    /**
     * Constuctor.
     *
     * @param RequestStack $request       Current request
     * @param Translator   $translator    Translation service
     * @param array        $configuration SEO configuration from umanit_tree key
     */
    public function __construct(RequestStack $request, Translator $translator, array $configuration)
    {
        $this->request       = $request->getCurrentRequest();
        $this->configuration = $configuration;
        $this->translator    = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            new \Twig_Function('get_seo_title', [$this, 'getSeoTitle']),
            new \Twig_Function('get_seo_description', [$this, 'getSeoDescription']),
            new \Twig_Function('get_seo_keywords', [$this, 'getSeoKeywords']),
        );
    }

    /**
     * Returns SEO title of the page. Puts the default value if not given.
     *
     * @param string $default  Default title for the page
     * @param int    $override Set default value if override is set to true
     *
     * @return string
     */
    public function getSeoTitle($default = '', $override = false)
    {
        if ($default && $override) {
            return $default;
        }

        if (!empty($this->request->attributes) && $contentObject = $this->request->attributes->get('contentObject', null)) {
            if ($contentObject instanceof SeoInterface && !empty($contentObject->getSeoTitle())) {
                return $contentObject->getSeoTitle();
            }
        }

        return $default ?: $this->translator->trans(
            $this->configuration['default_title'],
            array(),
            $this->configuration['translation_domain']
        );
    }

    /**
     * Returns SEO description of the page. Puts the default value if not given.
     *
     * @param string $default  Default title for the page
     * @param int    $override Set default value if override is set to true
     *
     * @return string
     */
    public function getSeoDescription($default = '', $override = false)
    {
        if ($default && $override) {
            return $default;
        }

        if (!empty($this->request->attributes) && $contentObject = $this->request->attributes->get('contentObject', null)) {
            if ($contentObject instanceof SeoInterface && !empty($contentObject->getSeoDescription())) {
                return $contentObject->getSeoDescription();
            }
        }

        return $default ?: $this->translator->trans(
            $this->configuration['default_description'],
            array(),
            $this->configuration['translation_domain']
        );
    }

    /**
     * Returns SEO keywords of the page. Puts the default value if not given.
     *
     * @param string $default  Default title for the page
     * @param int    $override Set default value if override is set to true
     *
     * @return string
     */
    public function getSeoKeywords($default = '', $override = false)
    {
        if ($default && $override) {
            return $default;
        }

        if (!empty($this->request->attributes) && $contentObject = $this->request->attributes->get('contentObject', null)) {
            if ($contentObject instanceof SeoInterface && !empty($contentObject->getSeoKeywords())) {
                return $contentObject->getSeoKeywords();
            }
        }

        return $default ?: $this->translator->trans(
            $this->configuration['default_keywords'],
            array(),
            $this->configuration['translation_domain']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'umanit_tree_seo';
    }
}
