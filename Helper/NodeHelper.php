<?php

namespace Umanit\TreeBundle\Helper;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Umanit\TreeBundle\Entity\Node;
use Umanit\TreeBundle\Event\NodeBeforeUpdateEvent;
use Umanit\TreeBundle\Event\NodeParentRegisterEvent;
use Umanit\TreeBundle\Event\NodeUpdatedEvent;
use Umanit\TreeBundle\Model\TreeNodeInterface;

class NodeHelper
{
    /**
     * Entities to delete.
     *
     * @var TreeNodeInterface[]
     */
    protected array $entitiesToRemove;

    /**
     * @param string                   $locale Default locale
     * @param ManagerRegistry          $doctrine
     * @param EventDispatcherInterface $eventDispatcher
     * @param EntityManagerInterface   $umanitTreeEntityManager
     */
    public function __construct(
        protected string $locale,
        protected ManagerRegistry $doctrine,
        protected EventDispatcherInterface $eventDispatcher,
        protected EntityManagerInterface $umanitTreeEntityManager
    ) {
        $this->entitiesToRemove = [];
    }

    /**
     * Update nodes related to an entity.
     */
    public function updateNodes(TreeNodeInterface $entity): void
    {
        $manager = $this->doctrine->getManager();

        $event = new NodeBeforeUpdateEvent($entity);
        $this->eventDispatcher->dispatch($event, NodeBeforeUpdateEvent::NAME);

        // Get tree nodes
        $treeNodes = $this->umanitTreeEntityManager->getRepository(Node::class)->findBy([
            'className' => $manager->getClassMetadata($entity::class)->getName(),
            'classId'   => $entity->getId(),
            'locale'    => $entity->getLocale(),
        ]);

        if (empty($treeNodes)) {
            $this->createNodes($entity);

            return;
        }

        $parents = $entity->getParents();
        if ($parents instanceof Collection) {
            $parents = $parents->toArray();
        }

        $event = new NodeParentRegisterEvent($entity, $parents);
        $this->eventDispatcher->dispatch($event, NodeParentRegisterEvent::NAME);

        $parents = $event->getParents();

        // Check if an entity changed its name
        foreach ($treeNodes as $treeNode) {
            // ROOT NODE CANNOT CHANGE ITS NAME
            if ($treeNode->getPath() === TreeNodeInterface::ROOT_NODE_PATH) {
                continue;
            }

            $nodeName = $entity->getTreeNodeName();
            if ($nodeName !== $treeNode->getNodeName()) {
                $treeNode->setNodeName($nodeName);

                $this->umanitTreeEntityManager->persist($treeNode);
                $this->umanitTreeEntityManager->flush($treeNode);
            }
        }

        $this->registerParents($entity, $treeNodes, $parents);

        $event = new NodeUpdatedEvent($entity);
        $this->eventDispatcher->dispatch($event, NodeUpdatedEvent::NAME);
    }

    /**
     * Register nodes to be removed (needs to call flush() in order to delete them).
     */
    public function prepareRemove(TreeNodeInterface $entity): void
    {
        $this->entitiesToRemove[] = [
            'id'     => $entity->getId(),
            'locale' => $entity->getLocale(),
            'name'   => $this->doctrine->getManager()->getClassMetadata($entity::class)->getName(),
        ];
    }

    /**
     * Execute nodes removal.
     */
    public function flush(): void
    {
        $entities = $this->entitiesToRemove;
        $this->entitiesToRemove = [];

        if (!empty($entities)) {
            foreach ($entities as $entity) {
                $nodes = $this->umanitTreeEntityManager->getRepository(Node::class)->findBy([
                    'className' => $entity['name'],
                    'classId'   => $entity['id'],
                    'locale'    => $entity['locale'],
                ]);

                foreach ($nodes as $node) {
                    $this->umanitTreeEntityManager->remove($node);
                    $this->umanitTreeEntityManager->flush($node);
                }
            }
        }
    }

    /**
     * Creates a new node.
     */
    private function createNodes(TreeNodeInterface $entity): void
    {
        $className = $this->doctrine->getManager()->getClassMetadata($entity::class)->getName();

        $event = new NodeBeforeUpdateEvent($entity);
        $this->eventDispatcher->dispatch($event, NodeBeforeUpdateEvent::NAME);

        $parents = $entity->getParents();
        if ($parents instanceof Collection) {
            $parents = $parents->toArray();
        }

        $event = new NodeParentRegisterEvent($entity, $parents);
        $this->eventDispatcher->dispatch($event, NodeParentRegisterEvent::NAME);

        $parents = $event->getParents();

        // Root node or not ?
        if ($entity->createRootNodeByDefault()) {
            // Principal node name
            $node = new Node();

            $node->setNodeName($entity->getTreeNodeName())
                 ->setClassName($className)
                 ->setClassId($entity->getId())
                 ->setLocale($entity->getLocale())
            ;

            $this->umanitTreeEntityManager->persist($node);
            $this->umanitTreeEntityManager->flush($node);
            $nodes = [$node];
        } else {
            $nodes = [];
        }

        $this->registerParents($entity, $nodes, $parents);

        $event = new NodeUpdatedEvent($entity);
        $this->eventDispatcher->dispatch($event, NodeUpdatedEvent::NAME);
    }

    /**
     * Register new parents added to a node.
     *
     * @param mixed  $entity    Entity that is registered
     * @param Node[] $treeNodes Tree node associated to the parent
     * @param array  $parents   Parents to register
     */
    private function registerParents(TreeNodeInterface $entity, array $treeNodes, array $parents): void
    {
        $manager = $this->doctrine->getManager();

        $nodeKeep = [];
        $nodeParents = $this->umanitTreeEntityManager->getRepository(Node::class)->findParentsNodesAsArray($parents);

        // Nodes from the parent
        if (!empty($nodeParents) && empty($treeNodes)) {
            foreach ($nodeParents as $node) {
                $newNode = new Node();

                $newNode->setNodeName($entity->getTreeNodeName())
                        ->setClassName($manager->getClassMetadata($entity::class)->getName())
                        ->setClassId($entity->getId())
                        ->setLocale($node['locale'])
                        ->setParent($this->umanitTreeEntityManager->getReference(Node::class, $node['id']))
                ;

                $this->umanitTreeEntityManager->persist($newNode);
                $this->umanitTreeEntityManager->flush($newNode);
            }
        } elseif (!empty($nodeParents)) {
            // Checks if we already have this parent
            foreach ($treeNodes as $treeNode) {
                foreach ($nodeParents as $node) {
                    if ($treeNode->getParent() && $treeNode->getParent()->getId() == $node['id']) {
                        $nodeKeep[] = $treeNode->getParent()->getId();
                        break;
                    }
                }
            }

            // If not, we create it
            if (count($nodeKeep) < (is_countable($nodeParents) ? count($nodeParents) : 0)) {
                foreach ($nodeParents as $node) {
                    if (!in_array($node['id'], $nodeKeep)) {
                        $newNode = new Node();

                        $newNode->setNodeName($entity->getTreeNodeName())
                                ->setClassName($manager->getClassMetadata($entity::class)->getName())
                                ->setClassId($entity->getId())
                                ->setLocale($node['locale'])
                                ->setParent($this->umanitTreeEntityManager->getReference(Node::class, $node['id']))
                        ;

                        $this->umanitTreeEntityManager->persist($newNode);
                        $this->umanitTreeEntityManager->flush($newNode);
                    }
                }
            }
        }

        // Delete nodes not used anymore
        foreach ($treeNodes as $treeNode) {
            // Delete root node ?
            if (!$treeNode->getManaged() ||
                (!$treeNode->getParent() && ($entity->createRootNodeByDefault() || empty($parents))) ||
                ($treeNode->getPath() == TreeNodeInterface::ROOT_NODE_PATH) ||
                (!empty($treeNode->getParent()) && in_array($treeNode->getParent()->getId(), $nodeKeep))) {
                continue;
            }

            $this->umanitTreeEntityManager->remove($treeNode);
            $this->umanitTreeEntityManager->flush();
        }
    }

    /**
     * Get the entity linked to the given node.
     *
     * @param Node|null $node
     *
     * @return TreeNodeInterface|null
     */
    public function getAssociatedEntity(?Node $node): ?TreeNodeInterface
    {
        if (!$node) {
            return null;
        }

        $repository = $this->doctrine->getRepository($node->getClassName());

        return $repository->findOneBy(['id' => $node->getClassId()]);
    }
}
