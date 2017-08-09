<?php

namespace Umanit\Bundle\TreeBundle\Helper;

use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Umanit\Bundle\TreeBundle\Entity\Node;
use Umanit\Bundle\TreeBundle\Model\TreeNodeInterface;
use Umanit\Bundle\TreeBundle\Event\NodeUpdatedEvent;
use Umanit\Bundle\TreeBundle\Event\NodeBeforeUpdateEvent;
use Umanit\Bundle\TreeBundle\Event\NodeParentRegisterEvent;
use Doctrine\Common\Collections\Collection;

class NodeHelper
{
    /**
     * @var string Default locale
     */
    protected $locale;

    /**
     * @var ManagerRegistry
     */
    protected $doctrine;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * Entities to delete.
     *
     * @var TreeNodeInterface[]
     */
    protected $entitiesToRemove;

    /**
     * @param string                   $locale          Default locale
     * @param ManagerRegistry          $doctrine
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct($locale, ManagerRegistry $doctrine, EventDispatcherInterface $eventDispatcher)
    {
        $this->locale           = $locale;
        $this->doctrine         = $doctrine;
        $this->eventDispatcher  = $eventDispatcher;
        $this->entitiesToRemove = [];
    }

    /**
     * Update nodes related to an entity.
     *
     * @param TreeNodeInterface $entity
     */
    public function updateNodes(TreeNodeInterface $entity)
    {
        $manager = $this->doctrine->getManager();

        $event = new NodeBeforeUpdateEvent($entity);
        $this->eventDispatcher->dispatch(NodeBeforeUpdateEvent::NAME, $event);

        // Get tree nodes
        $treeNodes = $manager->getRepository('UmanitTreeBundle:Node')->findBy(array(
            'className' => $manager->getClassMetadata(get_class($entity))->getName(),
            'classId'   => $entity->getId(),
            'locale'    => $entity->getLocale(),
        ));

        if (empty($treeNodes)) {
            $this->createNodes($entity);

            return;
        }

        $parents = $entity->getParents();
        if ($parents instanceof Collection) {
            $parents = $parents->toArray();
        }

        $event = new NodeParentRegisterEvent($entity, $parents);
        $this->eventDispatcher->dispatch(NodeParentRegisterEvent::NAME, $event);

        $parents = $event->getParents();

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

        $this->registerParents($entity, $treeNodes, $parents);

        $event = new NodeUpdatedEvent($entity);
        $this->eventDispatcher->dispatch(NodeUpdatedEvent::NAME, $event);
    }

    /**
     * Register nodes to be removed (needs to call flush() in order to delete them).
     *
     * @param TreeNodeInterface $entity
     */
    public function prepareRemove(TreeNodeInterface $entity)
    {
        $this->entitiesToRemove[] = array(
            'id'     => $entity->getId(),
            'locale' => $entity->getLocale(),
            'name'   => $this->doctrine->getManager()->getClassMetadata(get_class($entity))->getName(),
        );
    }

    /**
     * Execute nodes removal.
     */
    public function flush()
    {
        $manager = $this->doctrine->getManager();

        $entities               = $this->entitiesToRemove;
        $this->entitiesToRemove = array();

        if (!empty($entities)) {
            foreach ($entities as $entity) {
                $nodes = $manager->getRepository('UmanitTreeBundle:Node')->findBy(array(
                    'className' => $entity['name'],
                    'classId'   => $entity['id'],
                    'locale'    => $entity['locale'],
                ));

                foreach ($nodes as $node) {
                    $manager->remove($node);
                    $manager->flush($node);
                }
            }
        }
    }

    /**
     * Creates a new node.
     *
     * @param TreeNodeInterface $entity
     */
    private function createNodes(TreeNodeInterface $entity)
    {
        $manager   = $this->doctrine->getManager();
        $className = $this->doctrine->getManager()->getClassMetadata(get_class($entity))->getName();

        $event = new NodeBeforeUpdateEvent($entity);
        $this->eventDispatcher->dispatch(NodeBeforeUpdateEvent::NAME, $event);

        $parents = $entity->getParents();
        if ($parents instanceof Collection) {
            $parents = $parents->toArray();
        }

        $event = new NodeParentRegisterEvent($entity, $parents);
        $this->eventDispatcher->dispatch(NodeParentRegisterEvent::NAME, $event);

        $parents = $event->getParents();

        // Root node or not ?
        if ($entity->createRootNodeByDefault()) {
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
            $nodes = [$node];
        } else {
            $nodes = array();
        }

        $this->registerParents($entity, $nodes, $parents);

        $event = new NodeUpdatedEvent($entity);
        $this->eventDispatcher->dispatch(NodeUpdatedEvent::NAME, $event);
    }

    /**
     * Register new parents added to a node.
     *
     * @param mixed  $entity    Entity that is registered
     * @param Node[] $treeNodes Tree node associated to the parent
     * @param array  $parents   Parents to register
     */
    private function registerParents(TreeNodeInterface $entity, $treeNodes, array $parents)
    {
        $manager = $this->doctrine->getManager();

        $nodeKeep = array();

        // Entity parents
        foreach ($parents as $parent) {
            $nodes = null;
            if ($parent instanceof TreeNodeInterface) {
                $nodes = $manager->getRepository('UmanitTreeBundle:Node')->findBy([
                    'className' => $manager->getClassMetadata(get_class($parent))->getName(),
                    'classId'   => $parent->getId(),
                    'locale'    => $entity->getLocale(),
                ]);
            } elseif ($parent instanceof Node) {
                $nodes = [$parent];
            }

            // Nodes from the parent
            if (!empty($nodes) && empty($treeNodes)) {
                foreach ($nodes as $node) {
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
            } elseif (!empty($nodes)) {
                // Checks if we already have this parent
                $nodeExists = false;

                foreach ($treeNodes as $treeNode) {
                    foreach ($nodes as $node) {
                        if ($treeNode->getParent() && $treeNode->getParent()->getId() == $node->getId()) {
                            $nodeExists = true;
                            $nodeKeep[] = $treeNode->getParent()->getId();
                            break;
                        }
                    }
                }

                // If not, we create it
                if (!$nodeExists) {
                    foreach ($nodes as $node) {
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
                || (!$treeNode->getParent() && ($entity->createRootNodeByDefault() || empty($parents)))
                || ($treeNode->getPath() == TreeNodeInterface::ROOT_NODE_PATH)
                || (!empty($treeNode->getParent()) && in_array($treeNode->getParent()->getId(), $nodeKeep))
            ) {
                continue;
            }

            $manager->remove($treeNode);
            $manager->flush($treeNode);
        }
    }

    /**
     * Get the entity linked to the given node.
     *
     * @param Node $node
     *
     * @return mixed
     */
    public function getAssociatedEntity($node)
    {
        if (!$node) {
            return null;
        }

        $repository = $this->doctrine->getRepository($node->getClassName());
        $entity     = $repository->findOneBy(array('id' => $node->getClassId()));

        return $entity;
    }
}
