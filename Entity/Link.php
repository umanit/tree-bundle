<?php

namespace Umanit\TreeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Node
 *
 * @ORM\Table(name="treebundle_link")
 * @ORM\Entity()
 */
#[ORM\Table(name: 'treebundle_link')]
#[ORM\Entity]
class Link
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
     * @ORM\Column(name="uri", type="text", nullable=true)
     */
    #[ORM\Column(name: 'uri', type: 'text', nullable: true)]
    #[Assert\Url]
    protected ?string $externalLink = null;

    /**
     * @ORM\Column(name="internalUri", type="text", nullable=true)
     */
    #[ORM\Column(name: 'internalUri', type: 'text', nullable: true)]
    protected ?string $internalLink = null;

    /**
     * Assert that externalLink and internalLink are not filled
     *
     * @param ExecutionContextInterface $context
     * @param mixed                     $payload
     */
    #[Assert\Callback]
    public function validateNotBoth(ExecutionContextInterface $context, mixed $payload)
    {
        if ($this->externalLink && $this->internalLink) {
            $context->buildViolation('error.link.both_filled')
                    ->setTranslationDomain('UmanitTreeBundle')
                    ->addViolation()
            ;
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getExternalLink(): ?string
    {
        return $this->externalLink;
    }

    public function setExternalLink(?string $externalLink): self
    {
        $this->externalLink = $externalLink;

        return $this;
    }

    public function getInternalLink(): ?string
    {
        return $this->internalLink;
    }

    public function setInternalLink(?string $internalLink): self
    {
        $this->internalLink = $internalLink;

        return $this;
    }
}
