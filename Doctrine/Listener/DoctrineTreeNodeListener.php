<?php

namespace Umanit\TreeBundle\Doctrine\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Umanit\TreeBundle\Helper\NodeHelper;
use Umanit\TreeBundle\Model\TreeNodeInterface;

class DoctrineTreeNodeListener
{
    protected NodeHelper $nodeHelper;

    /**
     * @var array Nodes to update on flush.
     */
    protected array $nodesToUpdate = [];

    public function __construct(NodeHelper $nodeHelper)
    {
        $this->nodeHelper = $nodeHelper;
    }

    /**
     * Add a tree node to object if instanceof TreeNodeInterface.
     *
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        if ($entity instanceof TreeNodeInterface) {
            $this->nodesToUpdate[] = $entity;
        }
    }

    /**
     * Modify the tree node object if instanceof TreeNodeInterface
     * and the node is updated.
     *
     * @param LifecycleEventArgs $args
     */
    public function postUpdate(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        if ($entity instanceof TreeNodeInterface) {
            $this->nodesToUpdate[] = $entity;
        }
    }

    /**
     * Register all the nodes that will need to be remove.
     *
     * @param LifecycleEventArgs $args
     */
    public function preRemove(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        if ($entity instanceof TreeNodeInterface) {
            $this->nodeHelper->prepareRemove($entity);
        }
    }

    /**
     * Deletes all treenodes related to an entity.
     *
     * @param PostFlushEventArgs $args
     */
    public function postFlush(PostFlushEventArgs $args): void
    {
        $nodesToUpdate = $this->nodesToUpdate;
        $this->nodesToUpdate = [];

        foreach ($nodesToUpdate as $nodeToUpdate) {
            $this->nodeHelper->updateNodes($nodeToUpdate);
        }

        $this->nodeHelper->flush();
    }
}
