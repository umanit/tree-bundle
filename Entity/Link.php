<?php

namespace Umanit\Bundle\TreeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContext;

/**
 * Node
 *
 * @ORM\Table(name="treebundle_link")
 * @ORM\Entity()
 * @Gedmo\TranslationEntity(class="Umanit\Bundle\TreeBundle\Entity\Translation\LinkTranslation")
 * @Assert\Callback(methods={"validateNotBoth"})
 */
class Link
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
     * @ORM\Column(name="uri", type="text", nullable=true)
     * @Gedmo\Translatable()
     * @Assert\Url()
     */
    protected $externalLink;

    /**
     * @var string
     * @ORM\Column(name="internalUri", type="text", nullable=true)
     */
    protected $internalLink;

    /**
     * @ORM\OneToMany(targetEntity="Umanit\Bundle\TreeBundle\Entity\Translation\LinkTranslation", mappedBy="object", cascade={"persist", "remove"})
     */
    protected $translations;

    /**
     * Assert that externalLink and internalLink are not filled
     * @param ExecutionContext $context
     */
    public function validateNotBoth(ExecutionContext $context)
    {
        if ($this->externalLink && $this->internalLink) {
            $context
                ->buildViolation('error.link.both_filled')
                ->setTranslationDomain('UmanitTreeBundle')
                ->addViolation()
            ;
        }
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
     * Get the value of External Link
     *
     * @return string
     */
    public function getExternalLink()
    {
        return $this->externalLink;
    }

    /**
     * Set the value of External Link
     *
     * @param string $externalLink
     *
     * @return self
     */
    public function setExternalLink($externalLink)
    {
        $this->externalLink = $externalLink;

        return $this;
    }

    /**
     * Get the value of Internal Link
     *
     * @return string
     */
    public function getInternalLink()
    {
        return $this->internalLink;
    }

    /**
     * Set the value of Internal Link
     *
     * @param string $internalLink
     *
     * @return self
     */
    public function setInternalLink($internalLink)
    {
        $this->internalLink = $internalLink;

        return $this;
    }

    /**
     * Get the value of Translations
     *
     * @return array
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * Set the value of Translations
     *
     * @param array $translations
     * @return self
     */
    public function setTranslations($translations)
    {
        $this->translations = $translations;
        foreach ($this->translations as &$translation) {
            $translation->setObject($this);
        }

        return $this;
    }

    /**
     * Add a Translation
     *
     * @param mixed $translation
     * @return self
     */
    public function addTranslation($translation)
    {
        if (!$this->translations->contains($translation)) {
            $this->translations[] = $translation;
            $translation->setObject($this);
        }

        return $this;
    }
}
