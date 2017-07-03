<?php

namespace Umanit\Bundle\TreeBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\CallbackValidator;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormError;

class SeoMetadataType extends AbstractType
{

    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('url', 'text', array(
                'translation_domain' => $options['translation_domain'],
                'label' => 'seo.url',
                'required' => false
            ))
        ;
        $builder
            ->add('title', 'text', array(
                'translation_domain' => $options['translation_domain'],
                'label' => 'seo.title',
                'required' => false
            ))
        ;
        $builder
            ->add('description', 'textarea', array(
                'translation_domain' => $options['translation_domain'],
                'label' => 'seo.description',
                'required' => false
            ))
        ;
        $builder
            ->add('keywords', 'text', array(
                'translation_domain' => $options['translation_domain'],
                'label' => 'seo.keywords',
                'required' => false
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Umanit\Bundle\TreeBundle\Entity\SeoMetaData',
            'translation_domain' => 'UmanitTreeBundle'
        ));
    }


    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Umanit\Bundle\TreeBundle\Entity\SeoMetaData',
            'translation_domain' => 'UmanitTreeBundle'
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'umanit_seo_metadata_type';
    }
}
