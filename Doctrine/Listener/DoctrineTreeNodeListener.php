<?php

namespace Umanit\Bundle\TreeBundle\Doctrine\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Umanit\Bundle\TreeBundle\Entity\Node;
use Umanit\Bundle\TreeBundle\Model\TreeNodeInterface;
use Umanit\Bundle\TreeBundle\Event\NodeParentRegisterEvent;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class DoctrineTreeNodeListener
{
    /**
     * @var string Default locale
     */
    protected $locale;

    /**
     * Entities to delete.
     *
     * @var TreeNodeInterface[]
     */
    protected $entitiesToRemove;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * Constructor.
     *
     * @param string $locale Default locale
     */
    public function __construct($locale, EventDispatcherInterface $eventDispatcher)
    {
        $this->locale           = $locale;
        $this->eventDispatcher  = $eventDispatcher;
        $this->entitiesToRemove = array();
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
            $className = $manager->getClassMetadata(get_class($entity))->getName();

            // Root node or not ?
            if ($entity->createRootNodeByDefault() || !$entity->getParents()) {
                // Principal node name
                $node = new Node();
                $node
                    ->setNodeName($entity->getTreeNodeName())
                    ->setClassName($className)
                    ->setClassId($entity->getId())
                    ->setLocale($entity->getLocale())
                ;

                $manager->persist($node);
                $manager->flush($node);
            } else {
                $node = array();
            }

            $this->registerParents($entity, $manager, $node);
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
            // Get tree nodes
            $treeNodes = $manager->getRepository('UmanitTreeBundle:Node')->findBy(array(
                'className' => $manager->getClassMetadata(get_class($entity))->getName(),
                'classId'   => $entity->getId(),
                'locale'    => $entity->getLocale(),
            ));
            if (empty($treeNodes)) {
                $this->postPersist($args);

                return;
            }

            // Check if an entity changed its name
            foreach ($treeNodes as $treeNode) {
                // ROOT NODE CANNOT CHANGE ITS NAME
                if ($treeNode->getPath() === TreeNodeInterface::ROOT_NODE_PATH) {
                    continue;
                }

                $locale = $treeNode->getLocale();

                $nodeName = $entity->getTreeNodeName();
                if ($nodeName !== $treeNode->getNodeName()) {
                    $treeNode->setNodeName($nodeName);

                    $manager->persist($treeNode);
                    $manager->flush($treeNode);
                }
            }

            $this->registerParents($entity, $manager, $treeNodes);
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
            $this->entitiesToRemove[] = array(
                'id'     => $entity->getId(),
                'locale' => $entity->getLocale(),
                'name'   => $manager->getClassMetadata(get_class($entity))->getName(),
            );
        }
    }

    /**
     * Deletes all treenodes related to an entity.
     *
     * @param LifecycleEventArgs $args
     */
    public function postFlush(PostFlushEventArgs $args)
    {
        $manager = $args->getEntityManager();

        $entities               = $this->entitiesToRemove;
        $this->entitiesToRemove = array();

        if (!empty($this->entitiesToRemove)) {
            foreach ($entities as $entity) {
                $nodes = $manager->getRepository('UmanitTreeBundle:Node')->findBy(array(
                    'className' => $entity['name'],
                    'classId'   => $entity['id'],
                    'locale'    => $entity['locale'],
                ));

                foreach ($nodes as $node) {
                    $manager->remove($node);
                }
            }

            $manager->flush();
        }
    }

    /**
     * Register new parents added to a node.
     *
     * @param mixed         $entity    Entity that is registered
     * @param EntityManager $manager   Entity manager for ORM
     * @param Node[]        $treeNodes Tree node associated to the parent
     */
    private function registerParents($entity, $manager, $treeNodes)
    {
        $parents = $entity->getParents();
        if ($parents instanceof Collection) {
            $parents = $parents->toArray();
        }

        $event = new NodeParentRegisterEvent($entity, $parents);
        $this->eventDispatcher->dispatch(NodeParentRegisterEvent::NAME, $event);

        $parents = $event->getParents();

        $nodeKeep = array();

        // Entity parents
        foreach ($parents as $parent) {
            $node = null;
            if ($parent instanceof TreeNodeInterface) {
                $node = $manager->getRepository('UmanitTreeBundle:Node')->findOneBy(
                    array(
                        'className' => $manager->getClassMetadata(get_class($parent))->getName(),
                        'classId'   => $parent->getId(),
                        'locale'    => $entity->getLocale(),
                    )
                );
            } elseif ($parent instanceof Node) {
                $node = $parent;
            }

            // Nodes from the parent
            if (!empty($node) && empty($treeNodes)) {
                $newNode = new Node();
                $newNode
                    ->setNodeName($entity->getTreeNodeName())
                    ->setClassName($manager->getClassMetadata(get_class($entity))->getName())
                    ->setClassId($entity->getId())
                    ->setLocale($node->getLocale())
                    ->setParent($node)
                ;

                $manager->persist($newNode);
                $manager->flush($newNode);
            } elseif (!empty($node)) {
                foreach ($treeNodes as $treeNode) {
                    // Checks if we already have this parent
                    $nodeExists = false;

                    if ($treeNode->getParent() && $treeNode->getParent()->getId() == $node->getId()) {
                        $nodeExists = true;
                        $nodeKeep[] = $treeNode->getParent()->getId();
                        break;
                    }

                    // If not, we create it
                    if (!$nodeExists) {
                        $newNode = new Node();
                        $newNode
                            ->setNodeName($entity->getTreeNodeName())
                            ->setClassName($manager->getClassMetadata(get_class($entity))->getName())
                            ->setClassId($entity->getId())
                            ->setLocale($node->getLocale())
                            ->setParent($node)
                        ;

                        $manager->persist($newNode);
                        $manager->flush($newNode);
                    }
                }
            }
        }

        // Delete nodes not used anymore
        foreach ($treeNodes as $treeNode) {
            // Delete root node ?
            if (!$treeNode->getManaged()
                || (!$treeNode->getParent() && ($entity->createRootNodeByDefault() || !$entity->getParents()))
                || ($treeNode->getPath() == TreeNodeInterface::ROOT_NODE_PATH)
                || (!empty($treeNode->getParent()) && in_array($treeNode->getParent()->getId(), $nodeKeep))
            ) {
                continue;
            }

            $manager->remove($treeNode);
            $manager->flush($treeNode);
        }
    }
}
