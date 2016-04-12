<?php

namespace Umanit\Bundle\TreeBundle\Model;

use Umanit\Bundle\CmsBundle\Entity\Node;

/**
 * Interface to implement to manage nodes
 */
interface TreeNodeInterface
{
    const ROOT_NODE_PATH = 'umanit-root-node/';

    /**
     * @return string
     */
    public function getTreeNodeName();

    /**
     * Returns parents of the current node
     * @return mixed[]
     */
    public function getParents();

    /**
     * Create a root node by default or not
     * If not, one will be created if there's not result with getParents()
     *
     * @return bool
     */
    public function createRootNodeByDefault();
}
