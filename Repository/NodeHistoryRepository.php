<?php

namespace Umanit\TreeBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Umanit\TreeBundle\Entity\Node;
use Umanit\TreeBundle\Model\TreeNodeInterface;

class NodeHistoryRepository extends EntityRepository
{
    /**
     * Returns a node that match the given path for the given locale (if a locale is set, the "UNKNOWN_LOCALE" will be
     * added to the query).
     *
     * @param string $path   Slug to search
     * @param string $locale Locale of the object searched
     *
     * @return Node|null
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
                    $qb->expr()->eq('n.locale', ':unknown_locale')
                )
            );
            $qb->setParameter('locale', $locale);
            $qb->setParameter('unknown_locale', TreeNodeInterface::UNKNOWN_LOCALE);
        } else {
            $qb->andWhere('n.locale = :locale');
            $qb->setParameter('locale', TreeNodeInterface::UNKNOWN_LOCALE);
        }

        $qb->orderBy('n.id', 'DESC');
        $qb->setMaxResults(1);

        try {
            return $qb->getQuery()->getOneOrNullResult();
        } catch (\Exception) {
            return null;
        }
    }
}
