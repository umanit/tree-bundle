<?php

namespace Umanit\Bundle\TreeBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Umanit\Bundle\TreeBundle\Model\TreeNodeInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Menu
 *
 * @ORM\MappedSuperclass
 */
abstract class AbstractMenu
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
     * @var int
     *
     * @ORM\Column(name="oid", type="integer")
     */
    protected $oid;

    /**
     * @var string
     * @ORM\Column(name="locale", type="string", length=7, nullable=false)
     */
    protected $locale = TreeNodeInterface::UNKNOWN_LOCALE;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    protected $title;

    /**
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     *
     * @var \DateTime
     */
    protected $updatedAt;

    /**
     * @var int
     *
     * @ORM\Column(name="priority", type="integer")
     */
    protected $priority;

    /**
     * @var string
     *
     * @ORM\Column(name="position", type="string", length=16)
     */
    protected $position;

    /**
     * @var string
     *
     * @ORM\ManyToOne(targetEntity="Umanit\Bundle\TreeBundle\Entity\Link", fetch="EAGER", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="link_id", referencedColumnName="id")
     * @Assert\Valid()
     */
    protected $link;

    /**
     * Éléments enfants
     * @ORM\OneToMany(targetEntity="Menu", mappedBy="parent", cascade={"remove"})
     */
    protected $children;

    /**
     * Le parent. Si null alors noeud racine
     *
     * @ORM\ManyToOne(targetEntity="Menu", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     */
    protected $parent;

    /**
     * Le parent_id. Ruse pour le récupérant sans avoir besoin de charger l'object.
     *
     * @ORM\Column(name="parent_id", type="integer", nullable=true)
     */
    protected $parentId;

    /**
     * Menu constructor.
     */
    public function __construct()
    {
        $this->children = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return AbstractMenu
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set de la date de mise à jour.
     *
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function update()
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime $updatedAt
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * Set position
     *
     * @param string $position
     *
     * @return AbstractMenu
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position
     *
     * @return string
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @param int $priority
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;
    }

    /**
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * @param string $link
     */
    public function setLink($link)
    {
        $this->link = $link;
    }

    /**
     * @return mixed
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param mixed $children
     */
    public function setChildren($children)
    {
        $this->children = $children;
    }

    /**
     * @param AbstractMenu $child
     */
    public function addChildren($child)
    {
        $this->children->add($child);
    }

    /**
     * @return mixed
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param mixed $parent
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
    }

    /**
     * @return mixed
     */
    public function getParentId()
    {
        return $this->parentId;
    }

    /**
     * @param mixed $parentId
     */
    public function setParentId($parentId)
    {
        $this->parentId = $parentId;
    }

    /**
     * Set L'oid lors de la création.
     *
     * @ORM\PreFlush()
     */
    public function setDefaultOid()
    {
        if ($this->oid === null) {
            $this->oid = $this->getId();
        }
    }

    /**
     * @return int
     */
    public function getOid()
    {
        return $this->oid;
    }

    /**
     * @param int $oid
     */
    public function setOid($oid)
    {
        $this->oid = $oid;
    }

    /**
     * Return the document locale
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Set the locale of the document
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

    public function __clone()
    {
        $this->id = null;
    }

    /**
     * @return String le titre de l'objet
     */
    public function __toString()
    {
        if (!empty($this->title)) {
            return $this->getTitle();
        }

        return '';
    }
}
