<?php

namespace Umanit\Bundle\TreeBundle\Helper;

use Symfony\Component\PropertyAccess\Exception\AccessException;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * Title util class.
 *
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
class Title
{
    // The field names we do not want in a title.
    const STRIP_KEYS = [
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
    const FAV_KEYS = [
        'name',
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
     * Generate and return an entity's title.
     *
     * @param object $entity The object from which the title is generated.
     * @param int    $length The max length of the excerpt.
     *
     * @return string|null
     * @throws \ReflectionException
     */
    public function fromEntity($entity, $length = 100)
    {
        $refl = new \ReflectionClass($entity);
        // Parse every string attributes
        foreach ($refl->getProperties() as $property) {
            // Strip out unwanted values
            if (Str::striposInArray($property->getName(), self::STRIP_KEYS)) {
                continue;
            }

            // Get the value
            try {
                $value = $this->accessor->getValue($entity, $property->getName());
            } catch (AccessException $e) {
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
