<?php

namespace Umanit\TreeBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use Umanit\TreeBundle\Entity\SeoMetadata;

/**
 * Contains data usable for SEO.
 */
trait SeoTrait
{
    /**
     * @ORM\Embedded(class="Umanit\TreeBundle\Entity\SeoMetadata", columnPrefix=false)
     */
    #[ORM\Embedded(class: SeoMetadata::class, columnPrefix: false)]
    protected ?SeoMetadata $seoMetadata = null;

    public function getSeoMetadata(): ?SeoMetadata
    {
        return $this->seoMetadata;
    }

    public function setSeoMetadata(?SeoMetadata $seoMetadata): self
    {
        $this->seoMetadata = $seoMetadata;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSeoUrl(): ?string
    {
        return $this->seoMetadata == null ? '' : $this->seoMetadata->getUrl();
    }

    /**
     * {@inheritdoc}
     */
    public function getSeoTitle(): ?string
    {
        return $this->seoMetadata == null ? '' : $this->seoMetadata->getTitle();
    }

    /**
     * {@inheritdoc}
     */
    public function getSeoDescription(): ?string
    {
        return $this->seoMetadata == null ? '' : $this->seoMetadata->getDescription();
    }

    /**
     * {@inheritdoc}
     */
    public function getSeoKeywords(): ?string
    {
        return $this->seoMetadata == null ? '' : $this->seoMetadata->getKeywords();
    }
}
