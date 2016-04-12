<?php

namespace Umanit\Bundle\TreeBundle\Doctrine\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Umanit\Bundle\TreeBundle\Entity\Node;
use Umanit\Bundle\TreeBundle\Model\TreeNodeInterface;
use Umanit\Bundle\TreeBundle\Model\TranslationNodeInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\PostFlushEventArgs;

class DoctrineTreeNodeListener
{
    /**
     * @var string Default locale
     */
    protected $locale;

    /**
     * Entities to delete
     * @var TreeNodeInterface[]
     */
    protected $entitiesToRemove;

    /**
     * Constructor.
     * @param string $locale Default locale
     */
    public function __construct($locale)
    {
        $this->locale           = $locale;
        $this->entitiesToRemove = array();
    }

    /**
     * Add a tree node to object if instanceof TreeNodeInterface
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $entity  = $args->getObject();
        $manager = $args->getEntityManager();

        if ($entity instanceof TreeNodeInterface) {
            $className = $manager->getClassMetadata(get_class($entity))->getName();
            $nodes = array();

            // Root node or not ?
            if ($entity->createRootNodeByDefault() || !$entity->getParents()) {
                // Principal node name
                $node = new Node();
                $node
                    ->setNodeName($entity->getTreeNodeName())
                    ->setClassName($className)
                    ->setClassId($entity->getId())
                    ->setLocale($this->locale)
                ;

                $nodes[$this->locale] = array($node);
                $manager->persist($node);
                $manager->flush($node);
            } else {
                // No root node
                $nodes[$this->locale] = array();
            }

            // If mapped with Gedmo Personal translation
            if ($entity instanceof TranslationNodeInterface) {
                // All translated node names
                foreach ($entity->getTranslatedEntities() as $locale => $translation) {
                    // Root node
                    if ($entity->createRootNodeByDefault() || !$entity->getParents()) {
                        // If no translated node name, we get the entity node name
                        if (!$nodeName = $translation->getTreeNodeName()) {
                            $nodeName = $entity->getTreeNodeName();
                        }

                        $node = new Node();
                        $node
                            ->setNodeName($nodeName)
                            ->setClassName($className)
                            ->setClassId($entity->getId())
                            ->setLocale($locale)
                        ;

                        $nodes[$locale] = array($node);
                        $manager->persist($node);
                        $manager->flush($node);
                    } else {
                        // No root node
                        $nodes[$locale] = array();
                    }
                }
            }

            $this->registerParents($entity, $manager, $nodes);
        }
    }

    /**
     * Modify the tree node object if instanceof TreeNodeInterface
     * and the node is updated
     * @param LifecycleEventArgs $args
     */
    public function postUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        $manager = $args->getEntityManager();

        // If mapped with Gedmo Personal translation
        if ($entity instanceof TreeNodeInterface) {
            $nodes = array($this->locale => $entity);


            if ($entity instanceof TranslationNodeInterface) {
                $nodes = array_merge(
                    $nodes,
                    $entity->getTranslatedEntities()
                );
            }

            // Get tree nodes
            $treeNodes = $manager->getRepository('UmanitTreeBundle:Node')->findBy(array(
                'className' => $manager->getClassMetadata(get_class($entity))->getName(),
                'classId'   => $entity->getId()
            ));

            if (empty($treeNodes)) {
                $this->postPersist($args);
                return;
            }

            $allNodes = array();
            // Check if an entity changed its name
            foreach ($treeNodes as $treeNode) {
                // ROOT NODE CANNOT CHANGE ITS NAME
                if ($treeNode->getPath() === TreeNodeInterface::ROOT_NODE_PATH) {
                    continue;
                }

                $locale = $treeNode->getLocale();

                if (!isset($nodes[$locale])) {
                    continue;
                }

                if (!$nodeName = $nodes[$locale]->getTreeNodeName()) {
                    $nodeName = $entity->getTreeNodeName();
                }

                if ($nodeName !== $treeNode->getNodeName()) {
                    $treeNode->setNodeName($nodeName);

                    $manager->persist($treeNode);
                    $manager->flush($treeNode);
                }

                if (!isset($allNodes[$locale])) {
                    $allNodes[$locale] = array();
                }

                $allNodes[$locale][] = $treeNode;
            }

            // New nodes
            foreach ($nodes as $locale => $node) {
                if (isset($allNodes[$locale])) {
                    continue;
                }

                $nodeName = $node->getTreeNodeName();
                if (!$nodeName) {
                    $nodeName = $nodes[$this->locale]->getTreeNodeName();
                }

                $treeNode = new Node();
                $treeNode
                    ->setNodeName($nodeName)
                    ->setClassName($manager->getClassMetadata(get_class($entity))->getName())
                    ->setClassId($entity->getId())
                    ->setLocale($locale)
                ;

                $manager->persist($treeNode);
                $manager->flush($treeNode);
            }


            $this->registerParents($entity, $manager, $allNodes);
        }
    }

    /**
     * Register all the nodes that will need to be remove
     * @param LifecycleEventArgs $args
     */
    public function preRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        $manager = $args->getEntityManager();

        if ($entity instanceof TreeNodeInterface) {
            $this->entitiesToRemove[] = array(
                'id'   => $entity->getId(),
                'name' => $manager->getClassMetadata(get_class($entity))->getName()
            );
        }
    }

    /**
     * Deletes all treenodes related to an entity
     * @param LifecycleEventArgs $args
     */
    public function postFlush(PostFlushEventArgs $args)
    {
        $manager = $args->getEntityManager();

        $entities = $this->entitiesToRemove;
        $this->entitiesToRemove = array();

        foreach ($entities as $entity) {
            $nodes = $manager->getRepository('UmanitTreeBundle:Node')->findBy(array(
                'className' => $entity['name'],
                'classId'   => $entity['id']
            ));

            foreach ($nodes as $node) {
                $manager->remove($node);
            }

            $manager->flush();
        }
    }

    /**
     * Register new parents added to a node
     * @param mixed         $entity    Entity that is registered
     * @param EntityManager $manager   Entity manager for ORM
     * @param Node[]        $treeNodes Tree nodes associated to the parent
     */
    private function registerParents($entity, $manager, $treeNodes)
    {
        $parents = $entity->getParents();

        // Entity parents
        foreach ($parents as $parent) {
            if ($parent instanceof TreeNodeInterface) {
                $nodes = $manager->getRepository('UmanitTreeBundle:Node')->findBy(array(
                    'className' => $manager->getClassMetadata(get_class($parent))->getName(),
                    'classId'   => $parent->getId()
                ));

                // Nodes from the parent
                foreach ($nodes as $node) {
                    if (isset($treeNodes[$node->getLocale()])) {

                        // Checks if we already have this parent
                        $nodeExists = false;
                        foreach ($treeNodes[$node->getLocale()] as $key => $treeNode) {
                            if ($treeNode->getParent() && $treeNode->getParent()->getId() == $node->getId()) {
                                $nodeExists = true;
                                unset($treeNodes[$node->getLocale()][$key]);
                                break;
                            }
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
        }

        // Delete nodes not used anymore
        foreach ($treeNodes as $treeNodesLocal) {
            foreach ($treeNodesLocal as $treeNode) {
                // Delete root node ?
                if (!$treeNode->getManaged()
                    || (!$treeNode->getParent() && ($entity->createRootNodeByDefault()
                        || !$entity->getParents()))
                    || ($treeNode->getPath() == TreeNodeInterface::ROOT_NODE_PATH)

                ) {
                    continue;
                }

                $manager->remove($treeNode);
                $manager->flush();
            }
        }
    }
}
