<?php

namespace Umanit\TreeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;
use Umanit\TreeBundle\Repository\NodeHistoryRepository;

/**
 * Node
 *
 * @ORM\Table(name="treebundle_node_history", indexes={
 *     @ORM\Index(name="treebundle_node_history_search_idx", columns={"path", "locale"}),
 *     @ORM\Index(name="treebundle_node_history_search_idx_2", columns={"className", "classId", "locale"}),
 * })
 * @ORM\Entity(repositoryClass="Umanit\TreeBundle\Repository\NodeHistoryRepository")
 * @ORM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 */
#[ORM\Table(name: 'treebundle_node_history')]
#[ORM\Index(name: 'treebundle_node_history_search_idx', columns: ['path', 'locale'])]
#[ORM\Index(name: 'treebundle_node_history_search_idx_2', columns: ['className', 'classId', 'locale'])]
#[ORM\Entity(repositoryClass: NodeHistoryRepository::class)]
#[ORM\ChangeTrackingPolicy('DEFERRED_EXPLICIT')]
class NodeHistory
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
    protected ?string $path = null;

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
     * @ORM\Column(name="nodeName", type="string", length=255)
     */
    #[ORM\Column(name: 'nodeName', type: 'string', length: 255)]
    protected string $nodeName;

    /**
     * @ORM\Column(name="locale", type="string", length=10, nullable=true)
     */
    #[ORM\Column(name: 'locale', type: 'string', length: 10, nullable: true)]
    protected ?string $locale;

    public function getId(): ?int
    {
        return $this->id;
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
}
