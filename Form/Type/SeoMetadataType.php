<?php

namespace Umanit\Bundle\TreeBundle\Form\Type;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackValidator;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Umanit\Bundle\TreeBundle\Entity\SeoMetadata;
use Umanit\Bundle\TreeBundle\Model\TreeNodeInterface;
use Umanit\Bundle\TreeBundle\Router\NodeRouter;

class SeoMetadataType extends AbstractType
{
    /** @var NodeRouter */
    private $nodeRouter;

    /**
     * SeoMetadataType constructor.
     *
     * @param NodeRouter $nodeRouter
     */
    public function __construct(NodeRouter $nodeRouter)
    {
        $this->nodeRouter = $nodeRouter;
    }

    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('url', TextType::class, [
                'translation_domain' => $options['translation_domain'],
                'label'              => 'seo.url',
                'required'           => false,
            ])
            ->add('title', TextType::class, [
                'translation_domain' => $options['translation_domain'],
                'label'              => 'seo.title',
                'required'           => false,
            ])
            ->add('description', TextareaType::class, [
                'translation_domain' => $options['translation_domain'],
                'label'              => 'seo.description',
                'required'           => false,
            ])
            ->add('keywords', TextType::class, [
                'translation_domain' => $options['translation_domain'],
                'label'              => 'seo.keywords',
                'required'           => false,
            ])
        ;
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            if (null === $event->getData() || null === $event->getForm()->getParent()) {
                return;
            }
            /** @var SeoMetadata $seoMetadata */
            $seoMetadata = $event->getData();
            $seoForm     = $event->getForm();
            /** @var TreeNodeInterface $parentModelData */
            $parentModelData = $event->getForm()->getParent()->getData();

            if (null === $seoMetadata->getTitle()) {
                $this->setSubFormOption($seoForm, 'title', 'attr', [
                    'placeholder' => $parentModelData->getTreeNodeName(),
                ]);
            }

            if (null === $seoMetadata->getUrl()) {
                $this->setSubFormOption($seoForm, 'url', 'attr', [
                    'placeholder' => $this->nodeRouter->getPath(
                        $parentModelData,
                        null,
                        false,
                        false,
                        $parentModelData->getLocale()
                    )]);
            }
        });
    }

    /**
     * Set a form options.
     *
     * @param FormInterface $parentForm
     * @param  string       $childName
     * @param  string       $optionName
     * @param  mixed        $optionValue
     */
    protected function setSubFormOption(FormInterface $parentForm, $childName, $optionName, $optionValue)
    {
        $options = $parentForm->get($childName)->getConfig()->getOptions();

        $options[$optionName] = $optionValue;

        $parentForm->add(
            $childName,
            get_class($parentForm->get($childName)->getConfig()->getType()->getInnerType()),
            $options
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'         => SeoMetadata::class,
            'translation_domain' => 'UmanitTreeBundle',
        ]);
    }


    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class'         => SeoMetadata::class,
            'translation_domain' => 'UmanitTreeBundle',
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'umanit_seo_metadata_type';
    }
}
