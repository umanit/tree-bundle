<?php

namespace Umanit\Bundle\TreeBundle\Doctrine\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Umanit\Bundle\TreeBundle\Entity\Node;
use Umanit\Bundle\TreeBundle\Model\TreeNodeInterface;
use Umanit\Bundle\TreeBundle\Helper\NodeHelper;
use Doctrine\ORM\Event\PostFlushEventArgs;

class DoctrineTreeNodeListener
{
    /**
     * @var NodeHelper
     */
    protected $nodeHelper;

    /**
     * Constructor.
     *
     * @param string $locale Default locale
     */
    public function __construct(NodeHelper $nodeHelper)
    {
        $this->nodeHelper = $nodeHelper;
    }

    /**
     * Add a tree node to object if instanceof TreeNodeInterface.
     *
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $entity  = $args->getObject();
        $manager = $args->getEntityManager();

        if ($entity instanceof TreeNodeInterface) {
            $this->nodeHelper->updateNodes($entity);
        }
    }

    /**
     * Modify the tree node object if instanceof TreeNodeInterface
     * and the node is updated.
     *
     * @param LifecycleEventArgs $args
     */
    public function postUpdate(LifecycleEventArgs $args)
    {
        $entity  = $args->getObject();
        $manager = $args->getEntityManager();

        if ($entity instanceof TreeNodeInterface) {
            $this->nodeHelper->updateNodes($entity);
        }
    }

    /**
     * Register all the nodes that will need to be remove.
     *
     * @param LifecycleEventArgs $args
     */
    public function preRemove(LifecycleEventArgs $args)
    {
        $entity  = $args->getObject();
        $manager = $args->getEntityManager();

        if ($entity instanceof TreeNodeInterface) {
            $this->nodeHelper->prepareRemove($entity);
        }
    }

    /**
     * Deletes all treenodes related to an entity.
     *
     * @param LifecycleEventArgs $args
     */
    public function postFlush(PostFlushEventArgs $args)
    {
        $this->nodeHelper->flush();
    }
}
