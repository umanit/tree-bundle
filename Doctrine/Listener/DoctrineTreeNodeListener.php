<?php

namespace Umanit\TreeBundle\Doctrine\Listener;

use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Event\PreRemoveEventArgs;
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

    public function postPersist(PostPersistEventArgs $args): void
    {
        $entity = $args->getObject();

        if ($entity instanceof TreeNodeInterface) {
            $this->nodesToUpdate[] = $entity;
        }
    }

    public function postUpdate(PostUpdateEventArgs $args): void
    {
        $entity = $args->getObject();

        if ($entity instanceof TreeNodeInterface) {
            $this->nodesToUpdate[] = $entity;
        }
    }

    public function preRemove(PreRemoveEventArgs $args): void
    {
        $entity = $args->getObject();

        if ($entity instanceof TreeNodeInterface) {
            $this->nodeHelper->prepareRemove($entity);
        }
    }

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
