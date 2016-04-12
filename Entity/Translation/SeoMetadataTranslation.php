<?php

namespace Umanit\Bundle\TreeBundle\Entity\Translation;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Entity\MappedSuperclass\AbstractPersonalTranslation;

/**
 * @ORM\Entity
 * @ORM\Table(name="treebundle_seometadata_translations",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="seometadata_translations_idx", columns={
 *         "locale", "object_id", "field"
 *     })}
 * )
 */
class SeoMetadataTranslation extends AbstractPersonalTranslation
{
    /**
     * @ORM\ManyToOne(targetEntity="Umanit\Bundle\TreeBundle\Entity\SeoMetadata", inversedBy="translations")
     * @ORM\JoinColumn(name="object_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $object;

    /**
     * Convenient constructor
     *
     * @param string $locale
     * @param string $field
     * @param string $value
     */
    public function __construct($locale = null, $field = null, $value = null)
    {
        $this->setLocale($locale);
        $this->setField($field);
        $this->setContent($value);
    }
}
