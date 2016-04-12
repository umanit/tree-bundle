<?php

namespace Umanit\Bundle\TreeBundle\Model;

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
     * {@inheritDoc}
     */
    public function getParents()
    {
        return is_array($this->parents) ? $this->parents : array();
    }

    /**
     * Set parents node
     * @param mixed[] $parents
     */
    public function setParents(array $parents)
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
