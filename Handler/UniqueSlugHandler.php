<?php

namespace Umanit\Bundle\TreeBundle\Handler;

use Gedmo\Sluggable\Handler\SlugHandlerInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Gedmo\Sluggable\SluggableListener;
use Gedmo\Sluggable\Mapping\Event\SluggableAdapter;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;

class UniqueSlugHandler implements SlugHandlerInterface
{
    /**
     * @var ObjectManager
     */
    protected $om;

    /**
     * @var SluggableInterface
     */
    private $sluggable;

    /**
     * Construct the slug handler
     *
     * @param SluggableListener $sluggable
     */
    public function __construct(SluggableListener $sluggable)
    {
        $this->sluggable = $sluggable;
    }

    /**
     * Callback on slug handlers before the decision
     * is made whether or not the slug needs to be
     * recalculated
     *
     * @param SluggableAdapter $ea
     * @param array            $config
     * @param object           $object
     * @param string           $slug
     * @param boolean          $needToChangeSlug
     *
     * @return void
     */
    public function onChangeDecision(SluggableAdapter $ea, array &$config, $object, &$slug, &$needToChangeSlug)
    {
        $this->om = $ea->getObjectManager();
    }

    /**
     * Callback on slug handlers right after the slug is built
     *
     * @param SluggableAdapter $ea
     * @param array            $config
     * @param object           $object
     * @param string           $slug
     *
     * @return void
     */
    public function postSlugBuild(SluggableAdapter $ea, array &$config, $object, &$slug)
    {

    }

    /**
     * Callback for slug handlers on slug completion
     *
     * @param SluggableAdapter $ea
     * @param array            $config
     * @param object           $object
     * @param string           $slug
     *
     * @return void
     */
    public function onSlugCompletion(SluggableAdapter $ea, array &$config, $object, &$slug)
    {
        $index      = 1;
        $repository = $this->om->getRepository(get_class($object));

        $originalSlug = $slug;
        while ($retrieved = $repository->findOneBy(array('slug' => $slug, 'parent' => $object->getParent(), 'locale' => $object->getLocale()))) {
            if ($object->getId() == $retrieved->getId()) {
                break;
            }

            $slug = $originalSlug . '-' . $index;
            $index++;
        }
    }

    /**
     * @return boolean whether or not this handler has already urlized the slug
     */
    public function handlesUrlization()
    {
        return false;
    }

    /**
     * Validate handler options
     *
     * @param array         $options
     * @param ClassMetadata $meta
     */
    public static function validate(array $options, ClassMetadata $meta)
    {

    }
}
