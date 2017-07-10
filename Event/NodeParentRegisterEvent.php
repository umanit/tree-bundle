<?php

namespace Umanit\Bundle\TreeBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Umanit\Bundle\TreeBundle\Model\TreeNodeInterface;

/**
 * Event trigger when the bundle register the parents of a node.
 */
class NodeParentRegisterEvent extends Event
{
    const NAME = 'umanit.node.parent_register';

    /**
     * @var TreeNodeInterface
     */
    private $entity;

    /**
     * @var array
     */
    private $parents;

    /**
     * @param TreeNodeInterface $entity
     */
    public function __construct(TreeNodeInterface $entity, array $parents)
    {
        $this->entity  = $entity;
        $this->parents = $parents;
    }

    /**
     * @return TreeNodeInterface
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Get all parents of the entity.
     *
     * @return TreeNodeInterface[]
     */
    public function getParents()
    {
        return $this->parents;
    }

    /**
     * Set parents of the entity.
     *
     * @param array $parents
     */
    public function setParents(array $parents)
    {
        $this->parents = $parents;
    }
}
