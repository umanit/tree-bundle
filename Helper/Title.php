<?php

namespace Umanit\TreeBundle\Helper;

use Symfony\Component\PropertyAccess\Exception\AccessException;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class Title
{
    // The field names we do not want in a title.
    public const STRIP_KEYS = [
        'slug',
        'owner',
        'status',
        'sort',
        'locale',
        'siteaccess',
        'translation',
        'uuid',
        'image',
        'media',
        'video',
        'excerpt',
        'description',
        'intro',
        'introduction',
    ];

    // Friendly field names found in a title.
    public const FAV_KEYS = [
        'name',
        'title',
        'label',
        'appellation',
        'username',
        'nickname',
        'pseudonym',
        'denomination',
        'designation',
        'firstname',
        'lastname',
        'surname',
        'identity',
    ];

    public function __construct(private PropertyAccessor $accessor)
    {
    }

    /**
     * Generate and return an entity's title.
     *
     * @param object $entity The object from which the title is generated.
     * @param int    $length The max length of the excerpt.
     *
     * @return string|null
     */
    public function fromEntity(object $entity, int $length = 100): ?string
    {
        $refl = new \ReflectionClass($entity);
        $properties = $refl->getProperties();

        // Consider favourite keys first
        uasort($properties, function (\ReflectionProperty $a, \ReflectionProperty $b) {
            return match (true) {
                \in_array($a->getName(), $this::FAV_KEYS, true) => -1,
                \in_array($b->getName(), $this::FAV_KEYS, true) => 1,
                default => 0,
            };
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

            // If the field is one of the favourite
            // keys, directly return its value.
            if (false !== is_string($value) && Str::striposInArray($property->getName(), self::FAV_KEYS)) {
                return Html::trimText($value, $length);
            }
        }

        return null;
    }
}
