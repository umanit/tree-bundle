<?php

namespace Umanit\TreeBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Umanit\TreeBundle\Model\TreeNodeInterface;

/**
 * @ORM\MappedSuperclass
 */
#[ORM\MappedSuperclass]
abstract class AbstractMenu
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected ?int $id = null;

    /**
     * @ORM\Column(name="oid", type="integer")
     */
    #[ORM\Column(name: 'oid', type: 'integer')]
    protected ?int $oid = null;

    /**
     * @ORM\Column(name="locale", type="string", length=7, nullable=false)
     */
    #[ORM\Column(name: 'locale', type: 'string', length: 7)]
    protected string $locale = TreeNodeInterface::UNKNOWN_LOCALE;

    /**
     * @ORM\Column(name="title", type="string", length=255)
     */
    #[ORM\Column(name: 'title', type: 'string', length: 255)]
    protected string $title;

    /**
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     */
    #[ORM\Column(name: 'updated_at', type: 'datetime', nullable: true)]
    protected ?\DateTimeInterface $updatedAt = null;

    /**
     * @ORM\Column(name="priority", type="integer")
     */
    #[ORM\Column(name: 'priority', type: 'integer')]
    protected int $priority;

    /**
     * @ORM\Column(name="position", type="string", length=16)
     */
    #[ORM\Column(name: 'position', type: 'string', length: 16)]
    protected string $position;

    /**
     * @ORM\ManyToOne(targetEntity="Umanit\TreeBundle\Entity\Link", fetch="EAGER", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="link_id", referencedColumnName="id")
     */
    #[ORM\ManyToOne(targetEntity: Link::class, fetch: 'EAGER', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'link_id', referencedColumnName: 'id')]
    #[Assert\Valid]
    protected ?Link $link = null;

    /**
     * Éléments enfants
     *
     * @ORM\OneToMany(targetEntity="Menu", mappedBy="parent", cascade={"remove"})
     */
    #[ORM\OneToMany(targetEntity: 'Menu', mappedBy: 'parent', cascade: ['remove'])]
    protected Collection $children;

    /**
     * Le parent. Si null alors noeud racine
     *
     * @ORM\ManyToOne(targetEntity="Menu", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     */
    #[ORM\ManyToOne(targetEntity: 'Menu', inversedBy: 'children')]
    #[ORM\JoinColumn(name: 'parent_id', referencedColumnName: 'id')]
    protected $parent;

    /**
     * Le parent_id. Ruse pour le récupérant sans avoir besoin de charger l'object.
     *
     * @ORM\Column(name="parent_id", type="integer", nullable=true)
     */
    #[ORM\Column(name: 'parent_id', type: 'integer', nullable: true)]
    protected ?int $parentId = null;

    public function __construct()
    {
        $this->children = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Set de la date de mise à jour.
     *
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function update()
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function setPosition(string $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function getPosition(): string
    {
        return $this->position;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): self
    {
        $this->priority = $priority;

        return $this;
    }

    public function getLink(): ?Link
    {
        return $this->link;
    }

    public function setLink(?Link $link = null): self
    {
        $this->link = $link;

        return $this;
    }

    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function setChildren(Collection $children): self
    {
        $this->children = $children;

        return $this;
    }

    public function addChildren(AbstractMenu $child)
    {
        $this->children->add($child);
    }

    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param mixed $parent
     */
    public function setParent($parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    public function getParentId(): ?int
    {
        return $this->parentId;
    }

    public function setParentId(?int $parentId): self
    {
        $this->parentId = $parentId;

        return $this;
    }

    /**
     * Set L'oid lors de la création.
     *
     * @ORM\PreFlush()
     */
    #[ORM\PreFlush]
    public function setDefaultOid()
    {
        if ($this->oid === null) {
            $this->oid = $this->getId();
        }
    }

    public function getOid(): int
    {
        return $this->oid;
    }

    public function setOid(int $oid): self
    {
        $this->oid = $oid;

        return $this;
    }

    /**
     * Return the document locale
     *
     * @return string
     */
    public function getLocale(): ?string
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
    public function setLocale(string $locale): self
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
