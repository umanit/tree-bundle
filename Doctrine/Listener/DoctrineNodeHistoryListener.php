<?php

namespace Umanit\TreeBundle\Doctrine\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Umanit\TreeBundle\Entity\Node;
use Umanit\TreeBundle\Entity\NodeHistory;

class DoctrineNodeHistoryListener
{
    /**
     * @var string Default locale
     */
    protected string $locale;

    protected array $nodesToUpdate = [];

    protected array $nodesToRemove = [];

    public function __construct(string $locale)
    {
        $this->locale = $locale;
    }

    /**
     * Add a tree node to object if instanceof TreeNodeInterface.
     *
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        if ($entity instanceof Node) {
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

        if ($entity instanceof Node) {
            $this->nodesToUpdate[] = $entity;
        }
    }

    /**
     * Delete all NodeHistory.
     *
     * @param LifecycleEventArgs $args
     */
    public function postRemove(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        if ($entity instanceof Node) {
            $this->nodesToRemove[] = $entity;
        }
    }

    /**
     * Deletes all treenodes related to an entity.
     *
     * @param PostFlushEventArgs $args
     */
    public function postFlush(PostFlushEventArgs $args): void
    {
        $manager = $args->getObjectManager();

        $nodesToUpdate = $this->nodesToUpdate;
        $this->nodesToUpdate = [];

        foreach ($nodesToUpdate as $entity) {
            // Get tree nodes
            $treeNodes = $manager->getRepository(Node::class)->findBy([
                'path'      => $entity->getPath(),
                'className' => $entity->getClassName(),
                'classId'   => $entity->getClassId(),
                'locale'    => $entity->getLocale(),
            ]);

            if (empty($treeNodes)) {
                $nodeHistory = new NodeHistory();
                $nodeHistory
                    ->setPath($entity->getPath())
                    ->setNodeName($entity->getNodeName())
                    ->setClassName($entity->getClassName())
                    ->setClassId($entity->getClassId())
                    ->setLocale($entity->getLocale())
                ;

                $manager->persist($nodeHistory);
                $manager->flush($nodeHistory);

                return;
            }
        }

        $nodesToRemove = $this->nodesToRemove;
        $this->nodesToRemove = [];

        foreach ($nodesToRemove as $entity) {
            // Get tree nodes
            $treeNodes = $manager->getRepository(Node::class)->findBy([
                'className' => $entity->getClassName(),
                'classId'   => $entity->getClassId(),
                'locale'    => $entity->getLocale(),
            ]);
            if (empty($treeNodes)) {
                $nodes = $manager->getRepository(NodeHistory::class)->findBy([
                    'className' => $entity->getClassName(),
                    'classId'   => $entity->getClassId(),
                    'locale'    => $entity->getLocale(),
                ]);

                foreach ($nodes as $node) {
                    $manager->remove($node);
                }

                $manager->flush();
            }
        }
    }
}
