<?php

namespace Umanit\TreeBundle\Model;

/**
 * Methods to implement to have SEO working
 */
interface SeoInterface
{
    /**
     * Returns page Url
     *
     * @return string|null
     */
    public function getSeoUrl(): ?string;

    /**
     * Returns page title
     *
     * @return string|null
     */
    public function getSeoTitle(): ?string;

    /**
     * Returns page description
     *
     * @return string|null
     */
    public function getSeoDescription(): ?string;

    /**
     * Returns page keywords
     *
     * @return string|null
     */
    public function getSeoKeywords(): ?string;
}
