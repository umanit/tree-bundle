<?php

namespace Umanit\Bundle\TreeBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\CallbackValidator;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormError;

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
        $this->doctrine = $doctrine;
        $this->translator = $translator;
    }

    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Form builder
        if (!$options['allow_internal'] && !$options['allow_external']) {
            throw new \InvalidArgumentException('You must allow at least internal or external link on umanit_link_type');
        }

        if ($options['allow_external']) {
            $builder
                ->add('externalLink', 'text', array(
                    'translation_domain' => $options['translation_domain'],
                    'label' => 'link.external',
                    'required' => false
                ))
            ;
        }

        if ($options['allow_internal']) {
            $data = array();

            foreach ($options['models'] as $displayName => $classPath) {
                $repo = $this->doctrine->getRepository($classPath);
                $entities = $repo->findAll();

                $data[$displayName] = array();

                foreach ($entities as $entity) {
                    $data[$displayName][$entity->getId(). ';' . get_class($entity)] = $entity->__toString();
                }
            }

            $builder
                ->add('internalLink', 'choice', array(
                    'label'   => 'link.internal',
                    'translation_domain' => $options['translation_domain'],
                    'choices' => $data,
                    'attr' => array('class' => 'umanit-form-select2'),
                    'required' => false
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
                        $element->addError(new FormError('Vous devez spécifier au moins un lien interne ou externe'));
                    }
                }
            }
        });
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Umanit\Bundle\TreeBundle\Entity\Link',
            'models' => array(),
            'allow_internal' => true,
            'allow_external' => true,
            'translation_domain' => 'UmanitTreeBundle'
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'umanit_link_type';
    }
}
