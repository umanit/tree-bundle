<?php

namespace Umanit\Bundle\TreeBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Umanit\Bundle\TreeBundle\Model\TreeNodeInterface;

/**
 * Event trigger when the bundle has updated an entity.
 */
class NodeBeforeUpdateEvent extends Event
{
    const NAME = 'umanit.node.before_update';

    /**
     * @var TreeNodeInterface
     */
    private $entity;

    /**
     * @param TreeNodeInterface $entity
     */
    public function __construct(TreeNodeInterface $entity)
    {
        $this->entity  = $entity;
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
