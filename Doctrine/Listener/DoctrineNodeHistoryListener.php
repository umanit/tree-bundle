<?php
/**
 * Created by PhpStorm.
 * User: vgraillot
 * Date: 03/07/2017
 * Time: 15:16.
 */

namespace Umanit\Bundle\TreeBundle\Doctrine\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Umanit\Bundle\TreeBundle\Entity\Node;
use Umanit\Bundle\TreeBundle\Entity\NodeHistory;

class DoctrineNodeHistoryListener
{
    /**
     * @var string Default locale
     */
    protected $locale;

    /**
     * Constructor.
     *
     * @param string $locale Default locale
     */
    public function __construct($locale)
    {
        $this->locale           = $locale;
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

        if ($entity instanceof Node) {
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

        if ($entity instanceof Node) {
            // Get tree nodes
            $treeNodes = $manager->getRepository('UmanitTreeBundle:NodeHistory')->findBy(array(
                'path'      => $entity->getPath(),
                'className' => $entity->getClassName(),
                'classId'   => $entity->getClassId(),
                'locale'    => $entity->getLocale(),
            ));
            if (empty($treeNodes)) {
                $this->postPersist($args);

                return;
            }
        }
    }

    /**
     * Delete all NodeHistory.
     *
     * @param LifecycleEventArgs $args
     */
    public function postRemove(LifecycleEventArgs $args)
    {
        $entity  = $args->getObject();
        $manager = $args->getEntityManager();

        if ($entity instanceof Node) {
            // Get tree nodes
            $treeNodes = $manager->getRepository('UmanitTreeBundle:Node')->findBy(array(
                'className' => $entity->getClassName(),
                'classId'   => $entity->getClassId(),
                'locale'    => $entity->getLocale(),
            ));
            if (empty($treeNodes)) {
                $nodes = $manager->getRepository('UmanitTreeBundle:NodeHistory')->findBy(array(
                    'className' => $entity->getClassName(),
                    'classId'   => $entity->getClassId(),
                    'locale'    => $entity->getLocale(),
                ));

                foreach ($nodes as $node) {
                    $manager->remove($node);
                }

                $manager->flush();
            }
        }
    }
}
