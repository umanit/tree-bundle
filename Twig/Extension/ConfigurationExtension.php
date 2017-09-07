<?php

namespace Umanit\Bundle\TreeBundle\Twig\Extension;

/**
 * Class ConfigurationExtension
 *
 * @package Umanit\Bundle\TreeBundle\Twig\Extension
 */
class ConfigurationExtension extends \Twig_Extension implements \Twig_Extension_GlobalsInterface
{
    /**
     * @var string
     */
    private $adminLayout;

    /**
     * ConfigurationExtension constructor.
     *
     * @param string $adminLayout
     */
    public function __construct($adminLayout)
    {
        $this->adminLayout = $adminLayout;
    }

    /**
     * @inheritdoc
     *
     * @return array
     */
    public function getGlobals()
    {
        return [
            'umanit_tree_admin_layout' => $this->adminLayout,
        ];
    }
}
