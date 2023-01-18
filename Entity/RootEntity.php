<?php

namespace Umanit\TreeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * This entity is used to represent the root node
 * It can be replace by anything you want
 *
 * @ORM\Table(name="treebundle_root")
 * @ORM\Entity()
 */
#[ORM\Table(name: 'treebundle_root')]
#[ORM\Entity]
class RootEntity implements \Stringable
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

    public function getId(): ?int
    {
        return $this->id;
    }

    public function __toString(): string
    {
        return 'home';
    }
}
