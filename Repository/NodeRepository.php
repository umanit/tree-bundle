<?php

namespace Umanit\Bundle\TreeBundle\Repository;

use Umanit\Bundle\TreeBundle\Model\TreeNodeInterface;

class NodeRepository extends \Gedmo\Tree\Entity\Repository\MaterializedPathRepository
{
    /**
     * Returns a node that match the given slug for the given locale (if a locale is set, the "UNKNOWN_LOCALE" will be
     * added to the query)
     *
     * @param  string $slug   Slug to search
     * @param  string $locale Locale of the object searched
     * @param  mixed  $parent Parent of the object
     *
     * @return Node|null
     */
    public function getBySlug($slug, $locale = TreeNodeInterface::UNKNOWN_LOCALE, $parent = null)
    {
        $qb = $this
            ->createQueryBuilder('n')
            ->where('n.slug = :slug')
            ->setParameter('slug', $slug)
        ;

        $qbv = $this->getEntityManager()->createQueryBuilder();

        if ($locale !== TreeNodeInterface::UNKNOWN_LOCALE) {
            $qb->andWhere($qb->expr()->orX(
                $qb->expr()->eq('n.locale', ':locale'),
                $qb->expr()->eq('n.locale', ':unknow_locale')
            ));
            $qb->setParameter('locale', $locale);
            $qb->setParameter('unknow_locale', TreeNodeInterface::UNKNOWN_LOCALE);
        } else {
            $qb->andWhere('n.locale = :locale');
            $qb->setParameter('locale', TreeNodeInterface::UNKNOWN_LOCALE);
        }

        if ($parent) {
            $qb->andWhere('n.parent = :parent');
            $qb->setParameter('parent', $parent);
        }

        try {
            return $qb->getQuery()->getOneOrNullResult();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Returns a node that match the given path for the given locale (if a locale is set, the "UNKNOWN_LOCALE" will be
     * added to the query)
     *
     * @param  string $path   Slug to search
     * @param  string $locale Locale of the object searched
     *
     * @return Node|null
     */
    public function getByPath($path, $locale = TreeNodeInterface::UNKNOWN_LOCALE)
    {
        $qb = $this
            ->createQueryBuilder('n')
            ->where('n.path = :path')
            ->setParameter('path', $path)
        ;

        if ($locale !== TreeNodeInterface::UNKNOWN_LOCALE) {
            $qb->andWhere($qb->expr()->orX(
                $qb->expr()->eq('n.locale', ':locale'),
                $qb->expr()->eq('n.locale', ':unknow_locale')
            ));
            $qb->setParameter('locale', $locale);
            $qb->setParameter('unknow_locale', TreeNodeInterface::UNKNOWN_LOCALE);
        } else {
            $qb->andWhere('n.locale = :locale');
            $qb->setParameter('locale', TreeNodeInterface::UNKNOWN_LOCALE);
        }

        try {
            return $qb->getQuery()->getOneOrNullResult();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Search a node for the given class name and class identifier (if a locale is set, the "UNKNOWN_LOCALE" will be
     * added to the query)
     *
     * @param  string $className Class full namespace
     * @param  int    $classId   Class identifier
     * @param  Node   $parent    Node parent of the current node
     * @param  string $locale    Locale of the content
     *
     * @return Node|null
     */
    public function searchNode($className, $classId, $parent, $locale = TreeNodeInterface::UNKNOWN_LOCALE)
    {
        $qb = $this
            ->createQueryBuilder('n')
            ->where('n.className = :className')
            ->andWhere('n.classId = :classId')
            ->andWhere('n.parent = :parent')
            ->setParameter('className', $className)
            ->setParameter('classId', $classId)
            ->setParameter('parent', $parent)
        ;

        $qbv = $this->getEntityManager()->createQueryBuilder();

        if ($locale !== TreeNodeInterface::UNKNOWN_LOCALE) {
            $qb->andWhere($qbv->expr()->orX(
                $qbv->expr()->eq('n.locale', $locale),
                $qbv->expr()->eq('n.locale', TreeNodeInterface::UNKNOWN_LOCALE)
            ));
        } else {
            $qb->andWhere('n.locale = :locale');
            $qb->setParameter('locale', TreeNodeInterface::UNKNOWN_LOCALE);
        }

        try {
            return $qb->getQuery()->getOneOrNullResult();
        } catch (\Exception $e) {
            return null;
        }
    }
}
