<?php

namespace Umanit\TreeBundle\Model;

/**
 * Interface to implement to manage nodes
 */
interface TreeNodeInterface
{
    public const ROOT_NODE_PATH = '/umanit-root-node';
    public const UNKNOWN_LOCALE = 'unknown';

    public function getTreeNodeName(): string;

    /**
     * Returns parents of the current node
     *
     * @return mixed[]
     */
    public function getParents(): array;

    /**
     * Create a root node by default or not
     * If not, one will be created if there's not result with getParents()
     *
     * @return bool
     */
    public function createRootNodeByDefault(): bool;

    /**
     * Returns locale of the node
     *
     * @return string
     */
    public function getLocale(): ?string;
}
