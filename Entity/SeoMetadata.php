<?php

namespace Umanit\TreeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SeoMetadata
 *
 * @ORM\Embeddable
 */
#[ORM\Embeddable]
class SeoMetadata
{
    /**
     * @ORM\Column(name="seo_title", type="string", length=255, nullable=true)
     */
    #[ORM\Column(name: 'seo_title', type: 'string', length: 255, nullable: true)]
    protected ?string $title = null;

    /**
     * @ORM\Column(name="seo_description", type="text", nullable=true)
     */
    #[ORM\Column(name: 'seo_description', type: 'text', nullable: true)]
    protected ?string $description = null;

    /**
     * @ORM\Column(name="seo_keywords", type="text",  nullable=true)
     */
    #[ORM\Column(name: 'seo_keywords', type: 'text', nullable: true)]
    protected ?string $keywords = null;

    /**
     * @ORM\Column(name="seo_url", type="string", length=511, nullable=true)
     */
    #[ORM\Column(name: 'seo_url', type: 'string', length: 511, nullable: true)]
    protected ?string $url = null;

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getKeywords(): ?string
    {
        return $this->keywords;
    }

    public function setKeywords(?string $keywords): self
    {
        $this->keywords = $keywords;

        return $this;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setUrl(?string $url): self
    {
        $this->url = $url;
    }
}
