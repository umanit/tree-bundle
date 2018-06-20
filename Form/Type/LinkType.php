<?php

namespace Umanit\Bundle\TreeBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormError;
use Umanit\Bundle\TreeBundle\Entity\Link;

class LinkType extends AbstractType
{
    /**
     * @var Registry
     */
    protected $doctrine;

    /**
     * @var Translator
     */
    protected $translator;

    /**
     * @param Registry   $doctrine
     * @param Translator $translator
     */
    public function __construct(Registry $doctrine, Translator $translator)
    {
        $this->doctrine   = $doctrine;
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Form builder
        if (!$options['allow_internal'] && !$options['allow_external']) {
            throw new \InvalidArgumentException('You must allow at least internal or external link on umanit_link_type');
        }

        if ($options['allow_external']) {
            $builder
                ->add('externalLink', TextType::class, array(
                    'translation_domain' => $options['translation_domain'],
                    'label'              => $options['label_external'],
                    'required'           => false,
                ))
            ;
        }

        if ($options['allow_internal']) {
            $data = array();

            foreach ($options['models'] as $displayName => $classPath) {
                $repo     = $this->doctrine->getRepository($classPath);
                $filters  = $options['query_filters'][$classPath] ?? []
                ;
                $entities = $repo->findBy($filters);

                $data[$displayName] = array();

                foreach ($entities as $entity) {
                    $clazz = $this->doctrine->getManager()->getClassMetadata(get_class($entity))->getName();
                    $data[$displayName][$entity->__toString()] = $entity->getId().';'.$clazz;
                }
            }

            $builder
                ->add('internalLink', ChoiceType::class, array(
                    'label'              => $options['label_internal'],
                    'translation_domain' => $options['translation_domain'],
                    'choices'            => $data,
                    'attr'               => array('class' => 'umanit-form-select2'),
                    'required'           => false,
                ))
            ;
        }

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($options) {
            if ($options['required']) {
                $data = $event->getData();
                $form = $event->getForm();

                if (null === $data) {
                    return;
                }

                if (!$data->getInternalLink() && !$data->getExternalLink()) {
                    foreach ($form->all() as $element) {
                        $element->addError(new FormError('You must specify at least one external or internal link.'));
                    }
                }
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'         => Link::class,
            'models'             => array(),
            'query_filters'      => array(),
            'allow_internal'     => true,
            'allow_external'     => true,
            'translation_domain' => 'UmanitTreeBundle',
            'label_internal'     => 'link.internal',
            'label_external'     => 'link.external',
        ));
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'         => Link::class,
            'models'             => array(),
            'query_filters'      => array(),
            'allow_internal'     => true,
            'allow_external'     => true,
            'translation_domain' => 'UmanitTreeBundle',
            'label_internal'     => 'link.internal',
            'label_external'     => 'link.external',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'umanit_link_type';
    }
}
