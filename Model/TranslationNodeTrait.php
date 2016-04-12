<?php

namespace Umanit\Bundle\TreeBundle\Model;

use Umanit\Bundle\TreeBundle\Entity\Node;

/**
 * Translation node trait compatible with Personal Translations from Gedmo
 * @see https://github.com/Atlantic18/DoctrineExtensions/blob/master/doc/translatable.md
 */
trait TranslationNodeTrait
{
    /**
     * Get translations of an object
     *
     * array('locale' => 'entity')
     *
     * @return mixed[]
     */
    public function getTranslatedEntities()
    {
        $translations = array();

        foreach ($this->getTranslations() as $translation) {
            $field   = $translation->getField();
            $content = $translation->getContent();
            $locale  = $translation->getLocale();

            if (!isset($translations[$locale])) {
                $translations[$locale] = clone $this;
            }

            $setter = 'set' . ucfirst($field);
            $translations[$locale]->$setter($content);
        }

        return $translations;
    }
}
