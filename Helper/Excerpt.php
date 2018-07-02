<?php

namespace Umanit\Bundle\TreeBundle\Helper;

use Symfony\Component\PropertyAccess\Exception\AccessException;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * Excerpt util class.
 * Inspired by Bolt CMS.
 *
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
class Excerpt
{
    // The field names we do not want in an excerpt
    const STRIP_KEYS = [
        'slug',
        'username',
        'owner',
        'name',
        'title',
        'status',
        'sort',
        'locale',
        'siteaccess',
        'translation',
        'tuuid',
    ];

    // Friendly field names of an excert.
    const FAV_KEYS = [
        'excerpt',
        'description',
        'intro',
        'introduction',
    ];

    /**
     * @var PropertyAccessor
     */
    private $accessor;

    /**
     * Excerpt constructor.
     *
     * @param PropertyAccessor $accessor
     */
    public function __construct(PropertyAccessor $accessor)
    {
        $this->accessor = $accessor;
    }

    /**
     * Generate and return an entity's excerpt.
     *
     * @param object $entity The object from which the excerpt is generated.
     * @param int    $length The max length of the excerpt.
     *
     * @return string
     * @throws \ReflectionException
     */
    public function fromEntity($entity, $length = 300)
    {
        $values = '';
        $refl   = new \ReflectionClass($entity);
        // Parse every string attributes
        foreach ($refl->getProperties() as $property) {
            // Strip out unwanted values
            if (in_array(mb_strtolower($property->getName()), self::STRIP_KEYS, true)) {
                continue;
            }
            // Get the value
            try {
                $value = $this->accessor->getValue($entity, $property->getName());
            } catch (AccessException $e) {
                continue;
            }
            // We only need a string
            if (false === is_string($value)) {
                continue;
            }
            // If the field is one of the favourite
            // keys, directly return its value.
            foreach (self::FAV_KEYS as $favKey) {
                if (stripos($property->getName(), $favKey) || $favKey === mb_strtolower($property->getName())) {
                    return Html::trimText($value, $length);
                }
            }

            // If no field matches the favKeys, build-up an
            // array of strings that'll be used as the excerpt.
            $values .= ' '.$value;
        }

        return Html::trimText($values, $length);
    }

}
