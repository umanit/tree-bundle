<?php

namespace Umanit\Bundle\TreeBundle\Helper;

use Symfony\Bridge\Doctrine\ManagerRegistry;
use Umanit\Bundle\TreeBundle\Entity\Node;

class NodeHelper
{
    /**
     * @var ManagerRegistry
     */
    protected $doctrine;

    /**
     * @param ManagerRegistry $doctrine
     */
    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
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
