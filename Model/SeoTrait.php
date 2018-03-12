<?php

namespace Umanit\Bundle\TreeBundle\Model;

use Umanit\Bundle\TreeBundle\Entity\SeoMetadata;
use Doctrine\ORM\Mapping as ORM;

/**
 * Contains data usable for SEO.
 */
trait SeoTrait
{
    /**
     * @var SeoMetadata
     *
     * @ORM\Embedded(class="Umanit\Bundle\TreeBundle\Entity\SeoMetadata", columnPrefix=false)
     */
    protected $seoMetadata;

    /**
     * Get the value of Seo Metadata.
     *
     * @return SeoMetadata
     */
    public function getSeoMetadata()
    {
        return $this->seoMetadata;
    }

    /**
     * Set the value of Seo Metadata.
     *
     * @param SeoMetadata $seoMetadata
     *
     * @return self
     */
    public function setSeoMetadata(SeoMetadata $seoMetadata)
    {
        $this->seoMetadata = $seoMetadata;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSeoUrl()
    {
        return $this->seoMetadata == null ? '' : $this->seoMetadata->getUrl();
    }

    /**
     * {@inheritdoc}
     */
    public function getSeoTitle()
    {
        return $this->seoMetadata == null ? '' : $this->seoMetadata->getTitle();
    }

    /**
     * {@inheritdoc}
     */
    public function getSeoDescription()
    {
        return $this->seoMetadata == null ? '' : $this->seoMetadata->getDescription();
    }

    /**
     * {@inheritdoc}
     */
    public function getSeoKeywords()
    {
        return $this->seoMetadata == null ? '' : $this->seoMetadata->getKeywords();
    }
}
