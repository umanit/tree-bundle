<?php

namespace Umanit\TreeBundle\Handler;

use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\ObjectManager;
use Gedmo\Sluggable\Handler\SlugHandlerInterface;
use Gedmo\Sluggable\Mapping\Event\SluggableAdapter;
use Gedmo\Sluggable\SluggableListener;
use Umanit\TreeBundle\Entity\Node;

class UniqueSlugHandler implements SlugHandlerInterface
{
    protected ObjectManager $om;
    private SluggableListener $sluggable;

    public function __construct(SluggableListener $sluggable)
    {
        $this->sluggable = $sluggable;
    }

    /**
     * Callback on slug handlers before the decision
     * is made whether or not the slug needs to be
     * recalculated
     *
     * @param object $object
     * @param string $slug
     * @param bool   $needToChangeSlug
     */
    public function onChangeDecision(SluggableAdapter $ea, array &$config, $object, &$slug, &$needToChangeSlug)
    {
        $this->om = $ea->getObjectManager();
    }

    /**
     * Callback on slug handlers right after the slug is built.
     *
     * @param object $object
     * @param string $slug
     */
    public function postSlugBuild(SluggableAdapter $ea, array &$config, $object, &$slug)
    {
    }

    /**
     * Callback for slug handlers on slug completion.
     *
     * @param object $object
     * @param string $slug
     */
    public function onSlugCompletion(SluggableAdapter $ea, array &$config, $object, &$slug)
    {
        $index = 1;

        if ($object instanceof Node) {
            $repository = $this->om->getRepository($object::class);
            $originalSlug = $slug;

            while ($retrieved = $repository->getBySlug($slug, $object->getLocale(), $object->getParent())) {
                if ($object->getId() == $retrieved->getId()) {
                    break;
                }

                $slug = $originalSlug.'-'.$index;
                ++$index;
            }
        }
    }

    /**
     * @return bool whether or not this handler has already urlized the slug
     */
    public function handlesUrlization()
    {
        return false;
    }

    /**
     * Validate handler options
     */
    public static function validate(array $options, ClassMetadata $meta)
    {
    }
}
