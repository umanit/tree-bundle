<?php

namespace Umanit\TreeBundle\Repository;

use Gedmo\Tree\Entity\Repository\MaterializedPathRepository;
use Umanit\TreeBundle\Entity\Node;
use Umanit\TreeBundle\Model\TreeNodeInterface;

class NodeRepository extends MaterializedPathRepository
{
    /**
     * Returns a node that match the given slug for the given locale (if a locale is set, the "UNKNOWN_LOCALE" will be
     * added to the query).
     *
     * @param string $slug   Slug to search
     * @param string $locale Locale of the object searched
     * @param mixed  $parent Parent of the object
     */
    public function getBySlug(
        string $slug,
        string $locale = TreeNodeInterface::UNKNOWN_LOCALE,
        mixed $parent = null
    ): ?Node {
        $qb = $this
            ->createQueryBuilder('n')
            ->where('n.slug = :slug')
            ->setParameter('slug', $slug)
        ;

        if ($locale !== TreeNodeInterface::UNKNOWN_LOCALE) {
            $qb->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->eq('n.locale', ':locale'),
                    $qb->expr()->eq('n.locale', ':unknow_locale')
                )
            );
            $qb->setParameter('locale', $locale);
            $qb->setParameter('unknow_locale', TreeNodeInterface::UNKNOWN_LOCALE);
        } else {
            $qb->andWhere('n.locale = :locale');
            $qb->setParameter('locale', TreeNodeInterface::UNKNOWN_LOCALE);
        }

        if ($parent) {
            $qb->andWhere('n.parent = :parent');
            $qb->setParameter('parent', $parent);
        } else {
            $qb->andWhere('n.parent is null');
        }

        try {
            return $qb->getQuery()->getOneOrNullResult();
        } catch (\Exception) {
            return null;
        }
    }

    /**
     * Returns a node that match the given path for the given locale (if a locale is set, the "UNKNOWN_LOCALE" will be
     * added to the query).
     *
     * @param string $path   Slug to search
     * @param string $locale Locale of the object searched
     */
    public function getByPath(string $path, string $locale = TreeNodeInterface::UNKNOWN_LOCALE): ?Node
    {
        if ($path[0] !== '/') {
            $path = '/'.$path;
        }

        $qb = $this
            ->createQueryBuilder('n')
            ->where('n.path = :path')
            ->setParameter('path', $path)
        ;

        if ($locale !== TreeNodeInterface::UNKNOWN_LOCALE) {
            $qb->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->eq('n.locale', ':locale'),
                    $qb->expr()->eq('n.locale', ':unknow_locale')
                )
            );
            $qb->setParameter('locale', $locale);
            $qb->setParameter('unknow_locale', TreeNodeInterface::UNKNOWN_LOCALE);
        } else {
            $qb->andWhere('n.locale = :locale');
            $qb->setParameter('locale', TreeNodeInterface::UNKNOWN_LOCALE);
        }

        try {
            return $qb->getQuery()->getOneOrNullResult();
        } catch (\Exception) {
            return null;
        }
    }

    /**
     * Search a node for the given class name and class identifier (if a locale is set, the "UNKNOWN_LOCALE" will be
     * added to the query).
     *
     * @param string $className Class full namespace
     * @param int    $classId   Class identifier
     * @param Node[] $parents   Node parents allowed for the current node
     * @param string $locale    Locale of the content
     */
    public function searchNode(
        string $className,
        int $classId,
        array $parents,
        string $locale = TreeNodeInterface::UNKNOWN_LOCALE
    ): ?Node {
        $qb = $this
            ->createQueryBuilder('n')
            ->where('n.className = :className')
            ->andWhere('n.classId = :classId')
            ->setParameter('className', $className)
            ->setParameter('classId', $classId)
            ->setMaxResults(1)
        ;

        if ($locale !== TreeNodeInterface::UNKNOWN_LOCALE) {
            $qb->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->eq('n.locale', ':locale'),
                    $qb->expr()->eq('n.locale', ':unknow_locale')
                )
            );

            $qb->setParameter('locale', $locale);
            $qb->setParameter('unknow_locale', TreeNodeInterface::UNKNOWN_LOCALE);
        } else {
            $qb->andWhere('n.locale = :locale');
            $qb->setParameter('locale', TreeNodeInterface::UNKNOWN_LOCALE);
        }

        // Sort to get the most accurate result / Sort on condition is not supported on doctrine
        if ($parents) {
            $select = 'CASE ';
            foreach ($parents as $idx => $parentId) {
                $select = $select.'WHEN n.parent = '.$parentId.' THEN '.(count($parents) - $idx).' ';
            }

            $select .= 'ELSE 0 END AS HIDDEN parentSort';

            $qb->addSelect($select);
            $qb->addOrderBy('parentSort', 'desc');
        }

        $qb->addOrderBy('n.level', 'asc');

        try {
            return $qb->getQuery()->getOneOrNullResult();
        } catch (\Exception) {
            return null;
        }
    }

    /**
     * Returns all the nodes related to $parents.
     *
     * @param array $parents
     *
     * @return array
     */
    public function findParentsNodesAsArray(array $parents): array
    {
        $parentConditions = [];
        $nodeConditions = [];
        foreach ($parents as $parent) {
            if ($parent instanceof TreeNodeInterface) {
                $className = $this->_em->getClassMetadata($parent::class)->getName();
                $locale = $parent->getLocale();

                if (!isset($parentConditions[$className])) {
                    $parentConditions[$className] = [];
                }

                if (!isset($parentConditions[$className][$locale])) {
                    $parentConditions[$className][$locale] = [];
                }

                $parentConditions[$className][$locale][] = $parent->getId();
            } elseif ($parent instanceof Node) {
                $nodeConditions[] = $parent;
            }
        }

        $qb = $this->createQueryBuilder('n');

        // Instance of treeNode interface
        foreach ($parentConditions as $className => $parentCondition) {
            foreach ($parentCondition as $locale => $parents) {
                $uniqueKey = md5($className.$locale); // For parameters

                $qb->orWhere(
                    $qb->expr()->andX(
                        $qb->expr()->eq('n.className', ':className'.$uniqueKey),
                        $qb->expr()->eq('n.locale', ':locale'.$uniqueKey),
                        $qb->expr()->in('n.classId', ':classId'.$uniqueKey)
                    )
                );

                $qb->setParameter(':className'.$uniqueKey, $className);
                $qb->setParameter(':locale'.$uniqueKey, $locale);
                $qb->setParameter(':classId'.$uniqueKey, $parents);
            }
        }

        // Instance of node
        if (!empty($nodeConditions)) {
            $qb->orWhere($qb->expr()->in('n', ':nodes'));
            $qb->setParameter('nodes', $nodeConditions);
        }

        return (!empty($parents)) ? $qb->getQuery()->getArrayResult() : [];
    }
}
