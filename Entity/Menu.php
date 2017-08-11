<?php

namespace Umanit\Bundle\TreeBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Umanit\Bundle\TreeBundle\Model\TreeNodeInterface;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Menu
 *
 * @ORM\Table(name="menu")
 * @ORM\Entity(repositoryClass="Umanit\Bundle\TreeBundle\Repository\MenuRepository")
 * @ORM\HasLifecycleCallbacks()
 * @Vich\Uploadable
 */
class Menu
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="oid", type="integer")
     */
    private $oid;

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
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="image", type="string", length=255, nullable=true)
     */
    private $image;

    /**
     * @Vich\UploadableField(
     *     mapping="menu_image",
     *     fileNameProperty="image",
     * )
     * @Assert\Image(
     *     minWidth = 160,
     *     minHeight = 190
     * )
     *
     * @var File
     */
    private $imageFile;

    /**
     * @var string
     *
     * @ORM\Column(name="alt_image", type="string", length=255, nullable=true)
     */
    private $altImage;

    /**
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     *
     * @var \DateTime
     */
    private $updatedAt;

    /**
     * @var int
     *
     * @ORM\Column(name="priority", type="integer")
     */
    private $priority;

    /**
     * @var string
     *
     * @ORM\Column(name="position", type="string", length=16)
     */
    private $position;

    /**
     * @var string
     *
     * @ORM\ManyToOne(targetEntity="Umanit\Bundle\TreeBundle\Entity\Link", fetch="EAGER", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="link_id", referencedColumnName="id")
     */
    private $link;

    /**
     * Éléments enfants
     * @ORM\OneToMany(targetEntity="Menu", mappedBy="parent", cascade={"remove"})
     */
    private $children;

    /**
     * Le parent. Si null alors noeud racine
     *
     * @ORM\ManyToOne(targetEntity="Menu", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     */
    private $parent;

    /**
     * Le parent_id. Ruse pour le récupérant sans avoir besoin de charger l'object.
     *
     * @ORM\Column(name="parent_id", type="integer", nullable=true)
     */
    private $parentId;

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
     * @return Menu
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
     * Set image.
     *
     * @param string $image
     *
     * @return Menu
     */
    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * get image.
     *
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the  update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     *
     * @param File|\Symfony\Component\HttpFoundation\File\UploadedFile $image
     *
     * @return Menu
     */
    public function setImageFile($image = null)
    {
        $this->imageFile = $image;

        return $this;
    }

    /**
     * Retourne l'image.
     *
     * @return File|null
     */
    public function getImageFile()
    {
        return $this->imageFile;
    }

    /**
     * @return string
     */
    public function getAltImage()
    {
        return $this->altImage;
    }

    /**
     * @param string $altImage
     */
    public function setAltImage($altImage)
    {
        $this->altImage = $altImage;
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
     * @return Menu
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
     * @param Menu $child
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
        } else {
            return '';
        }
    }
}
