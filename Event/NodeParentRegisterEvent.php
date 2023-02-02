<?php

namespace Umanit\TreeBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;
use Umanit\TreeBundle\Model\TreeNodeInterface;

/**
 * Event triggered when the bundle registers the parents of a node
 */
class NodeParentRegisterEvent extends Event
{
    public const NAME = 'umanit.node.parent_register';

    public function __construct(private TreeNodeInterface $entity, private array $parents)
    {
    }

    public function getEntity(): TreeNodeInterface
    {
        return $this->entity;
    }

    /**
     * Get all parents of the entity.
     *
     * @return TreeNodeInterface[]
     */
    public function getParents(): array
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
