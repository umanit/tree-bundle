<?php

namespace Umanit\Bundle\TreeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * This entity is used to represent the root node
 * It can be replace by anything you want
 *
 * @ORM\Table(name="treebundle_root")
 * @ORM\Entity()
 */
class RootEntity
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
     * @return string
     */
    public function __toString()
    {
        return 'home';
    }
}
