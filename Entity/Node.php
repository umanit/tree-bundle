<?php

namespace Umanit\TreeBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Umanit\TreeBundle\Handler\UniqueSlugHandler;
use Umanit\TreeBundle\Repository\NodeRepository;

/**
 * Node.
 *
 * @ORM\Table(name="treebundle_node",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="search_idx", columns={"path", "locale"})},
 *     indexes={
 *     @ORM\Index(name="search_idx_slug", columns={"slug"}),
 *     @ORM\Index(name="search_idx_2", columns={"className", "classId", "locale"}),
 *     @ORM\Index(name="search_idx_3", columns={"className", "classId"})
 * })
 * @ORM\Entity(repositoryClass="Umanit\TreeBundle\Repository\NodeRepository")
 * @ORM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 */

#[ORM\Table(name: 'treebundle_node')]
#[ORM\Index(name: 'search_idx_slug', columns: ['slug'])]
#[ORM\Index(name: 'search_idx_2', columns: ['className', 'classId', 'locale'])]
#[ORM\Index(name: 'search_idx_3', columns: ['className', 'classId'])]
#[ORM\UniqueConstraint(name: 'search_idx', columns: ['path', 'locale'])]
#[ORM\Entity(repositoryClass: NodeRepository::class)]
#[ORM\ChangeTrackingPolicy('DEFERRED_EXPLICIT')]
#[Gedmo\Tree(type: 'materializedPath')]
class Node
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
     * @ORM\Column(name="path", type="string", length=255, nullable=true)
     */
    #[ORM\Column(name: 'path', type: 'string', length: 255, nullable: true)]
    #[Gedmo\TreePath(separator: '/', appendId: false, startsWithSeparator: true, endsWithSeparator: false)]
    protected ?string $path = null;

    /**
     * @ORM\Column(name="slug", type="string")
     */
    #[ORM\Column(name: 'slug', type: 'string')]
    #[Gedmo\TreePathSource()]
    #[Gedmo\Slug(fields: ['nodeName'], unique: false)]
    #[Gedmo\SlugHandler(class: UniqueSlugHandler::class)]
    protected string $slug;

    /**
     * @ORM\ManyToOne(targetEntity="Umanit\TreeBundle\Entity\Node", inversedBy="children")
     * @ORM\JoinColumn(name="parentId", referencedColumnName="id", onDelete="CASCADE")
     */
    #[ORM\ManyToOne(targetEntity: Node::class, inversedBy: 'children')]
    #[ORM\JoinColumn(name: 'parentId', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[Gedmo\TreeParent]
    protected ?Node $parent = null;

    /**
     * @ORM\OneToMany(targetEntity="Umanit\TreeBundle\Entity\Node", mappedBy="parent", cascade={"persist"})
     */
    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: Node::class, cascade: ['persist'])]
    protected Collection $children;

    /**
     * @ORM\Column(name="className", type="string", length=255, nullable=true)
     */
    #[ORM\Column(name: 'className', type: 'string', length: 255, nullable: true)]
    protected ?string $className = null;

    /**
     * @ORM\Column(name="classId", type="integer", nullable=true)
     */
    #[ORM\Column(name: 'classId', type: 'integer', nullable: true)]
    protected ?int $classId = null;

    /**
     * @ORM\Column(name="level", type="integer", nullable=true)
     */
    #[ORM\Column(name: 'level', type: 'integer', nullable: true)]
    #[Gedmo\TreeLevel]
    protected ?int $level = null;

    /**
     * @ORM\Column(name="nodeName", type="string", length=255)
     */
    #[ORM\Column(name: 'nodeName', type: 'string', length: 255)]
    protected string $nodeName;

    /**
     * @ORM\Column(name="locale", type="string", length=10, nullable=true)
     */
    #[ORM\Column(name: 'locale', type: 'string', length: 10, nullable: true)]
    protected ?string $locale = null;

    /**
     * @ORM\Column(name="managed", type="boolean", nullable=true)
     */
    #[ORM\Column(name: 'managed', type: 'boolean', nullable: true)]
    protected bool $managed;

    public function __construct()
    {
        $this->managed = true;
        $this->children = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getParent(): ?Node
    {
        return $this->parent;
    }

    public function setParent(?Node $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    public function removeParent(): self
    {
        $this->parent = null;

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

    public function removeChild(Node $child): self
    {
        $this->children->removeElement($child);

        return $this;
    }

    public function removeChildren(): self
    {
        $this->children->clear();

        return $this;
    }

    public function getClassName(): ?string
    {
        return $this->className;
    }

    public function setClassName(?string $className): self
    {
        $this->className = $className;

        return $this;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(?string $path): self
    {
        $this->path = $path;

        return $this;
    }

    public function getLevel(): ?int
    {
        return $this->level;
    }

    public function setLevel(?int $level): self
    {
        $this->level = $level;

        return $this;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getNodeName(): string
    {
        return $this->nodeName;
    }

    public function setNodeName(string $nodeName): self
    {
        $this->nodeName = $nodeName;

        return $this;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setLocale(?string $locale): self
    {
        $this->locale = $locale;

        return $this;
    }

    public function getClassId(): ?int
    {
        return $this->classId;
    }

    public function setClassId(?int $classId): self
    {
        $this->classId = $classId;

        return $this;
    }

    public function getManaged(): bool
    {
        return $this->managed;
    }

    public function setManaged(bool $managed): self
    {
        $this->managed = $managed;

        return $this;
    }
}
