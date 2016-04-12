<?php

namespace Umanit\Bundle\TreeBundle\Model;

/**
 * TranslationNodeInterface
 *
 * Used to get entity in other locales
 */
interface TranslationNodeInterface
{
    /**
     * Returns Gedmo Personal Translations
     *
     * @return mixed[]
     */
    public function getTranslations();

    /**
     * Get translations of an object
     *
     * array('locale' => 'entity')
     *
     * @return mixed[]
     */
    public function getTranslatedEntities();
}
