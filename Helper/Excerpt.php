<?php

namespace Umanit\TreeBundle\Helper;

use Symfony\Component\PropertyAccess\Exception\AccessException;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * Excerpt util class.
 * Inspired by Bolt CMS.
 */
class Excerpt
{
    // The field names we do not want in an excerpt
    public const STRIP_KEYS = [
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
        'uuid',
        'image',
        'media',
        'video',
    ];

    // Friendly field names found in an excerpt.
    public const FAV_KEYS = [
        'excerpt',
        'description',
        'intro',
        'introduction',
    ];

    public function __construct(private PropertyAccessor $accessor)
    {
    }

    /**
     * Generate and return an entity's excerpt.
     *
     * @param object $entity The object from which the excerpt is generated.
     * @param int    $length The max length of the excerpt.
     *
     * @throws \ReflectionException
     */
    public function fromEntity(object $entity, int $length = 150): string
    {
        $values = '';
        $refl = new \ReflectionClass($entity);
        $properties = $refl->getProperties();

        // Consider favourite keys first
        uasort($properties, function (\ReflectionProperty $a, \ReflectionProperty $b) {
            if (\in_array($a->getName(), $this::FAV_KEYS, true)) {
                return -1;
            }
            if (\in_array($b->getName(), $this::FAV_KEYS, true)) {
                return 1;
            }

            return 0;
        });

        // Parse every string attributes
        foreach ($properties as $property) {
            // Strip out unwanted values
            if (Str::striposInArray($property->getName(), self::STRIP_KEYS)) {
                continue;
            }

            // Get the value
            try {
                $value = $this->accessor->getValue($entity, $property->getName());
            } catch (AccessException) {
                continue;
            }

            // Try to convert the value in a string
            if (is_object($value) && method_exists($value, '__toString')) {
                $value = (string) $value;
            }

            if (false === is_string($value)) {
                continue;
            }

            // If the field is one of the favourite
            // keys, directly return its value.
            if (Str::striposInArray($property->getName(), self::FAV_KEYS)) {
                return Html::trimText($value, $length);
            }

            // If no field matches the favKeys, build-up an
            // array of strings that'll be used as the excerpt.
            $values .= ' '.$value;
        }

        return Html::trimText($values, $length);
    }
}
