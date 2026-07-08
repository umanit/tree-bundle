<?php

namespace Umanit\TreeBundle\Doctrine\Listener;

use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
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

    public function postPersist(PostPersistEventArgs $args): void
    {
        $entity = $args->getObject();

        if ($entity instanceof Node) {
            $this->nodesToUpdate[] = $entity;
        }
    }

    public function postUpdate(PostUpdateEventArgs $args): void
    {
        $entity = $args->getObject();

        if ($entity instanceof Node) {
            $this->nodesToUpdate[] = $entity;
        }
    }

    public function postRemove(PostRemoveEventArgs $args): void
    {
        $entity = $args->getObject();

        if ($entity instanceof Node) {
            $this->nodesToRemove[] = $entity;
        }
    }

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
                $manager->flush();

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
