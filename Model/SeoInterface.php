<?php

namespace Umanit\Bundle\TreeBundle\Model;

/**
 * Methods to implement to have SEO working
 */
interface SeoInterface
{
    /**
     * Returns page Url
     * @return string
     */
    public function getSeoUrl();

    /**
     * Returns page title
     * @return string
     */
    public function getSeoTitle();

    /**
     * Returns page description
     * @return string
     */
    public function getSeoDescription();

    /**
     * Returns page keywords
     * @return array
     */
    public function getSeoKeywords();
}
