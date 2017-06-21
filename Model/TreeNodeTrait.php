<?php

namespace Umanit\Bundle\TreeBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use Umanit\Bundle\TreeBundle\Model\TreeNodeInterface;

/**
 * Tree node trait
 */
trait TreeNodeTrait
{
    /**
     * @var mixed[]
     */
    protected $parents;

    /**
     * @var string
     * @ORM\Column(name="locale", type="string", length=7)
     */
    protected $locale = TreeNodeInterface::UNKNOWN_LOCALE;

    /**
     * Return the document locale
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Set the locale of the document
     *
     * @param string $locale
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getParents()
    {
        return empty($this->parents) ? array() : $this->parents;
    }

    /**
     * Set parents node
     * @param mixed[] $parents
     */
    public function setParents($parents)
    {
        $this->parents = $parents;
    }

    /**
     * Create a root node by default or not
     * If not, one will be created if there's not result with getParents()
     *
     * @return bool
     */
    public function createRootNodeByDefault()
    {
        return true;
    }
}
