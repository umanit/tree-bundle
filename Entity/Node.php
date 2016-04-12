<?php

namespace Umanit\Bundle\TreeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Node
 *
 * @ORM\Table(name="treebundle_node", indexes={
 *     @ORM\Index(name="search_idx_slug", columns={"slug"}),
 *     @ORM\Index(name="search_idx", columns={"path", "locale"}),
 *     @ORM\Index(name="search_idx_2", columns={"className", "classId", "locale"}),
 *     @ORM\Index(name="search_idx_3", columns={"className", "classId"})
 * })
 * @ORM\Entity(repositoryClass="Gedmo\Tree\Entity\Repository\MaterializedPathRepository")
 * @ORM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 * @Gedmo\Tree(type="materializedPath")
 */
class Node
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="path", type="string", length=255, nullable=true)
     * @Gedmo\TreePath(separator="/", appendId=false, startsWithSeparator=false, endsWithSeparator=false)
     */
    protected $path;

    /**
     * @var int
     *
     * @Gedmo\TreePathSource()
     * @Gedmo\Slug(handlers={
     *     @Gedmo\SlugHandler(class="Umanit\Bundle\TreeBundle\Handler\UniqueSlugHandler")
     * }, fields={"nodeName"}, unique=false)
     * @ORM\Column(name="slug", type="string")
     */
    protected $slug;

    /**
     * @var Node
     *
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="Umanit\Bundle\TreeBundle\Entity\Node", inversedBy="children")
     * @ORM\JoinColumn(name="parentId", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $parent;

    /**
     * @var Node[]
     *
     * @ORM\OneToMany(targetEntity="Umanit\Bundle\TreeBundle\Entity\Node", mappedBy="parent", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $children;

    /**
     * @var string Object class
     *
     * @ORM\Column(name="className", type="string", length=255, nullable=true)
     */
    protected $className;

    /**
     * @var int Object Id
     *
     * @ORM\Column(name="classId", type="integer", nullable=true)
     */
    protected $classId;

    /**
     * @var int
     *
     * @Gedmo\TreeLevel
     * @ORM\Column(name="level", type="integer", nullable=true)
     */
    protected $level;

    /**
     * @var string
     *
     * @ORM\Column(name="nodeName", type="string", length=255)
     */
    protected $nodeName;

    /**
     * @var string
     *
     * @ORM\Column(name="locale", type="string", length=10, nullable=true)
     */
    protected $locale;

    /**
     * @var bool
     *
     * @ORM\Column(name="managed", type="boolean", nullable=true)
     */
    protected $managed;

    public function __construct()
    {
        $this->managed  = true;
        $this->children = new ArrayCollection();
    }

    /**
     * Get the value of Id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the value of Id
     *
     * @param int $id
     *
     * @return self
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of Parent
     *
     * @return Node
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set the value of Parent
     *
     * @param Node $parent
     *
     * @return self
     */
    public function setParent(Node $parent)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Remove parent
     *
     * @return self
     */
    public function removeParent()
    {
        $this->parent = null;

        return $this;
    }

    /**
     * Get the value of Children
     *
     * @return Node[]
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Set the value of Children
     *
     * @param Node[] $children
     *
     * @return self
     */
    public function setChildren($children)
    {
        $this->children = $children;

        return $this;
    }

    /**
     * Remove a child
     *
     * @param Node[] $child
     *
     * @return self
     */
    public function removeChild($child)
    {
        $this->children->removeElement($child);

        return $this;
    }

    /**
     * Clear the value of Children
     *
     * @return self
     */
    public function removeChildren()
    {
        $this->children->clear();

        return $this;
    }

    /**
     * Get the value of Class
     *
     * @return string Object class
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * Set the value of Class
     *
     * @param string $className
     *
     * @return self
     */
    public function setClassName($className)
    {
        $this->className = $className;

        return $this;
    }

    /**
     * Get the value of Path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set the value of Path
     *
     * @param string $path
     *
     * @return self
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get the value of Level
     *
     * @return int
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * Set the value of Level
     *
     * @param int $level
     *
     * @return self
     */
    public function setLevel($level)
    {
        $this->level = $level;

        return $this;
    }

    /**
     * Get the value of Slug
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set the value of Slug
     *
     * @param string $slug
     *
     * @return self
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get the value of Node Name
     *
     * @return string
     */
    public function getNodeName()
    {
        return $this->nodeName;
    }

    /**
     * Set the value of Node Name
     *
     * @param string $nodeName
     *
     * @return self
     */
    public function setNodeName($nodeName)
    {
        $this->nodeName = $nodeName;

        return $this;
    }

    /**
     * Get the value of Locale
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Set the value of Locale
     *
     * @param string $locale
     *
     * @return self
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Get the value of Class Id
     *
     * @return int Object Id
     */
    public function getClassId()
    {
        return $this->classId;
    }

    /**
     * Set the value of Class Id
     *
     * @param int $classId Object Id
     *
     * @return self
     */
    public function setClassId($classId)
    {
        $this->classId = $classId;

        return $this;
    }

    /**
     * Get the value of Managed
     *
     * @return bool
     */
    public function getManaged()
    {
        return $this->managed;
    }

    /**
     * Set the value of Managed
     *
     * @param bool $managed
     *
     * @return self
     */
    public function setManaged($managed)
    {
        $this->managed = $managed;

        return $this;
    }

}
