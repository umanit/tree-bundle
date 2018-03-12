<?php

namespace Umanit\Bundle\TreeBundle\Entity;

use Umanit\Bundle\TreeBundle\Model\SeoInterface;
use Umanit\Bundle\TreeBundle\Entity\Translation\SeoMetadataTranslation;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * SeoMetadata
 * @ORM\Embeddable
 */
class SeoMetadata
{
    /**
     * @var string
     * @ORM\Column(name="seo_title", type="string", length=255, nullable=true)
     */
    protected $title;

    /**
     * @var string
     * @ORM\Column(name="seo_description", type="text", nullable=true)
     */
    protected $description;

    /**
     * @var string
     * @ORM\Column(name="seo_keywords", type="text",  nullable=true)
     */
    protected $keywords;

    /**
     * @var string
     * @ORM\Column(name="seo_url", type="string", length=511, nullable=true)
     */
    protected $url;

    /**
     * Get the value of Title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set the value of Title
     *
     * @param string $title
     *
     * @return self
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get the value of Description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set the value of Description
     *
     * @param string $description
     *
     * @return self
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get the value of Keywords
     *
     * @return string
     */
    public function getKeywords()
    {
        return $this->keywords;
    }

    /**
     * Set the value of Keywords
     *
     * @param string $keywords
     *
     * @return self
     */
    public function setKeywords($keywords)
    {
        $this->keywords = $keywords;

        return $this;
    }
    
    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }
}
