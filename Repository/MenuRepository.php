<?php

namespace Umanit\TreeBundle\Repository;

use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Umanit\TreeBundle\Entity\Link;

/**
 * @todo AGU : Translate comments.
 */
class MenuRepository extends EntityRepository
{
    /**
     * Récupération du menu à plat
     *
     * @param string $identifier
     *
     * @return array
     */
    public function getMenu(string $identifier = 'primary'): array
    {
        // Build select part
        $table = $this->getClassMetadata()->table['name'];
        $menuSelect = $this->buildSelectPart($table);
        $secondMenuSelect = $this->buildSelectPart('c');

        $sql = <<<SQL
with recursive menu_tree as (
    select $menuSelect
    , link_id
    , 1 as level
    , array[priority]::integer[] as path_priority
   from $table
   where parent_id is null
     and $table.position = :identifier
   union all
   select $secondMenuSelect
    , c.link_id
    , p.level + 1
    , p.path_priority||c.priority
   from $table c
     join menu_tree p on c.parent_id = p.id
   where c.position = :identifier
)
SELECT %SELECT%, mt.level
FROM menu_tree mt
LEFT OUTER JOIN treebundle_link l ON mt.link_id = l.id
order by path_priority
SQL;

        $rsm = new ResultSetMappingBuilder($this->_em);
        $rsm->addRootEntityFromClassMetadata($this->getClassMetadata()->name, 'mt');
        $rsm->addJoinedEntityFromClassMetadata(Link::class, 'l', 'mt', 'link', ['id' => 'address_id']);

        $sql = strtr($sql, ['%SELECT%' => $rsm->generateSelectClause()]);

        $query = $this->_em->createNativeQuery($sql, $rsm);
        $query->setParameter('identifier', $identifier);

        return $query->getResult(Query::HYDRATE_ARRAY);
    }

    /**
     * Récupération du menu à plat indenté (pour les select en BO)
     *
     * @param string $identifier
     *
     * @return AbstractMenu[]
     */
    public function getIndentMenu(string $identifier = 'primary'): array
    {
        $cols = $this->getClassMetadata()->getColumnNames();

        unset($cols['title']);

        // Build select part
        $table = $this->getClassMetadata()->table['name'];
        $menuSelect = $this->buildSelectPart($table, $cols);
        $secondMenuSelect = $this->buildSelectPart('c', $cols);

        $sql = <<<SQL
with recursive menu_tree as (
    select $menuSelect
    , link_id
    , $table.title::text AS cast_title
    , 1 as level
    , array[priority]::integer[] as path_priority
   from $table
   where parent_id is null
     and $table.position = :identifier
   union all
   select $secondMenuSelect
    , c.link_id
    , rpad('', p.level * 4, '\xC2\xA0')::text||c.title::text
    , p.level + 1
    , p.path_priority||c.priority
   from $table c
     join menu_tree p on c.parent_id = p.id
   where c.position = :identifier
)
SELECT %SELECT%, mt.level
FROM menu_tree mt
LEFT OUTER JOIN treebundle_link l ON mt.link_id = l.id
order by path_priority
SQL;

        $rsm = new ResultSetMappingBuilder($this->_em);
        $rsm->addRootEntityFromClassMetadata($this->getClassMetadata()->name, 'mt');
        $rsm->addJoinedEntityFromClassMetadata(Link::class, 'l', 'mt', 'link', ['id' => 'address_id']);

        $sql = strtr($sql, ['%SELECT%' => $rsm->generateSelectClause()]);

        $query = $this->_em->createNativeQuery($sql, $rsm);
        $query->setParameter('identifier', $identifier);

        return $query->getResult();
    }

    /**
     * Récupération d'une partie du menu (pour la mise à jour
     *
     * @param int|null $parentId Identifiant du parent
     *
     * @return array
     * @throws Exception
     */
    public function subIdMenu(?int $parentId): array
    {
        $selectSQL = ' = :parent_id';
        if ($parentId == null) {
            $selectSQL = ' IS NULL';
        }

        $table = $this->getClassMetadata()->table['name'];

        $sql = <<<SQL
SELECT id 
FROM $table
WHERE parent_id $selectSQL
ORDER BY priority
SQL;

        $query = $this->_em->getConnection()->prepare($sql);

        if ($parentId != null) {
            $query->bindValue('parent_id', $parentId);
        }

        $result = $query->executeQuery();

        return $result->fetchAllAssociative();
    }

    /**
     * Déplace un menu dans un nouvel emplacement
     *
     * @param int|null $parentId      Identifiant du noeud parent
     * @param int      $currentNodeId Identifiant du noeud à déplacer
     * @param int[]    $newMenu       Ordonnancement du menu
     *
     * @return int Nombre d'object modifié
     * @throws Exception
     */
    public function moveMenu(?int $parentId, int $currentNodeId, array $newMenu): int
    {
        $paramSQL = [];

        if ($parentId == null) {
            $parentIdOperatorSQL = ' IS';
            $parentIdSQL = ' NULL';
            $paramSQL[] = $currentNodeId;
        } else {
            $parentIdOperatorSQL = ' = ';
            $parentIdSQL = ' ?::int ';
            $paramSQL[] = $parentId;
            $paramSQL[] = $currentNodeId;
            $paramSQL[] = $parentId;
        }

        $case = '';
        $lastIndex = 0;
        foreach ($newMenu as $index => $menuId) {
            $case .= ' WHEN ?::int THEN ?::int';
            $paramSQL[] = $menuId;
            $paramSQL[] = $index;
            $lastIndex = $index;
        }
        $case .= ' ELSE ?::int ';
        $paramSQL[] = ++$lastIndex;

        $table = $this->getClassMetadata()->table['name'];

        $sql = <<<SQL
WITH source AS (
    SELECT * FROM $table
    WHERE parent_id $parentIdOperatorSQL $parentIdSQL
    UNION SELECT * FROM $table where id = ?::int
),
orderable AS (
SELECT id, title, $parentIdSQL::int as parent_id,
CASE id
%CASE%
END
as priority FROM source
ORDER BY priority)
update $table set parent_id=orderable.parent_id, priority=orderable.priority
FROM orderable
WHERE $table.id = orderable.id
SQL;

        $sql = strtr($sql, ['%CASE%' => $case]);

        return $this->_em->getConnection()->executeStatement($sql, $paramSQL);
    }

    /**
     * Récupération du menu à plat pour le front
     *
     * @param string $locale langue voulue
     *
     * @return array
     */
    public function getFrontMenu(string $locale): array
    {
        // Build select part
        $table = $this->getClassMetadata()->table['name'];
        $menuSelect = $this->buildSelectPart($table);
        $secondMenuSelect = $this->buildSelectPart('c');

        $sql = <<<SQL
with recursive menu_tree as (
    select $menuSelect
    , link_id
    , 1 as level
    , array[priority]::integer[] as path_priority
   from $table
   where parent_id is null and ("locale" = :locale or "locale" = 'unknown')
   union all
   select $secondMenuSelect
    , c.link_id
    , p.level + 1
    , p.path_priority||c.priority
   from $table c
     join menu_tree p on c.parent_id = p.id
   where c."locale" = :locale or c."locale" = 'unknown'
)
SELECT %SELECT%, mt.level
FROM menu_tree mt
LEFT OUTER JOIN treebundle_link l ON mt.link_id = l.id
order by path_priority
SQL;

        $rsm = new ResultSetMappingBuilder($this->_em);
        $rsm->addEntityResult($this->getClassMetadata()->name, 'mt');

        foreach ($this->getClassMetadata()->fieldNames as $col => $field) {
            $rsm->addFieldResult('mt', $col, $field);
        }

        $rsm->addJoinedEntityFromClassMetadata(Link::class, 'l', 'mt', 'link', ['id' => 'address_id']);

        $sql = strtr($sql, ['%SELECT%' => $rsm->generateSelectClause()]);

        $query = $this->_em->createNativeQuery($sql, $rsm);

        $query->setParameter('locale', $locale);

        return $query->getResult();
    }

    /**
     * Builds select part from class metadata
     *
     * @param null  $alias
     * @param array $columnNames
     *
     * @return string
     */
    protected function buildSelectPart($alias = null, array $columnNames = []): string
    {
        if (empty($columnNames)) {
            $columnNames = $this->getClassMetadata()->getColumnNames();
        }

        return implode(
            ', ',
            array_map(function ($colname) use ($alias) {
                if ($alias) {
                    return sprintf('%s.%s', $alias, $colname);
                }

                return null;
            }, $columnNames)
        );
    }
}
