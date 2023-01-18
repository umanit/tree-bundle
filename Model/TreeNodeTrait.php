<?php

namespace Umanit\TreeBundle\Model;

use Doctrine\ORM\Mapping as ORM;

trait TreeNodeTrait
{
    /**
     * @var mixed[]
     */
    protected array $parents;

    /**
     * @ORM\Column(name="locale", type="string", length=7)
     */
    #[ORM\Column(name: 'locale', type: 'string', length: 7)]
    protected string $locale = TreeNodeInterface::UNKNOWN_LOCALE;

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

    /**
     * {@inheritDoc}
     */
    public function getParents(): array
    {
        return empty($this->parents) ? [] : $this->parents;
    }

    public function setParents(array $parents): self
    {
        $this->parents = $parents;

        return $this;
    }

    /**
     * Create a root node by default or not
     * If not, one will be created if there's not result with getParents()
     *
     * @return bool
     */
    public function createRootNodeByDefault(): bool
    {
        return true;
    }
}
