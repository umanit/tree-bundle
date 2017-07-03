<?php

namespace Umanit\Bundle\TreeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Node
 *
 * @ORM\Table(name="treebundle_node_history", indexes={
 *     @ORM\Index(name="treebundle_node_history_search_idx", columns={"path", "locale"}),
 *     @ORM\Index(name="treebundle_node_history_search_idx_2", columns={"className", "classId", "locale"}),
 * })
 * @ORM\Entity(repositoryClass="Umanit\Bundle\TreeBundle\Repository\NodeHistoryRepository")
 * @ORM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 */
class NodeHistory
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
     */
    protected $path;

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

}
