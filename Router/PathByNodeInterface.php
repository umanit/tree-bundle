<?php
/**
 * Created by PhpStorm.
 * User: vmerlet
 * Date: 31/08/2018
 * Time: 11:08
 */

namespace Umanit\Bundle\TreeBundle\Router;

/**
 * Allow to override the seo url for a specific entity
 */
interface PathByNodeInterface
{
    /**
     * Returns the relative path to access the node.
     *
     * @param bool  $absolute
     * @param array $parameters
     *
     * @return string
     */
    public function getPathByNode($absolute = false, array $parameters = []);
}
