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
use Symfony\Component\Translation\TranslatorInterface;
use Umanit\Bundle\TreeBundle\Entity\SeoMetadata;
use Umanit\Bundle\TreeBundle\Helper\Excerpt;
use Umanit\Bundle\TreeBundle\Helper\Title;
use Umanit\Bundle\TreeBundle\Model\TreeNodeInterface;
use Umanit\Bundle\TreeBundle\Router\NodeRouter;

class SeoMetadataType extends AbstractType
{
    /** @var NodeRouter */
    private $nodeRouter;

    /** @var Excerpt */
    private $excerpt;

    /** @var array */
    private $seoConfig;

    /**  @var TranslatorInterface */
    private $translator;

    /** @var Title */
    private $title;

    /**
     * SeoMetadataType constructor.
     *
     * @param NodeRouter          $nodeRouter
     * @param Excerpt             $excerpt
     * @param Title               $title
     * @param TranslatorInterface $translator
     * @param array               $seoConfig
     */
    public function __construct(
        NodeRouter $nodeRouter,
        Excerpt $excerpt,
        Title $title,
        TranslatorInterface $translator,
        array $seoConfig
    ) {
        $this->nodeRouter = $nodeRouter;
        $this->excerpt    = $excerpt;
        $this->title      = $title;
        $this->seoConfig  = $seoConfig;
        $this->translator = $translator;
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

        // Add placeholders to seo fields.
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            if (null === $event->getData() || null === $event->getForm()->getParent()) {
                return;
            }
            /** @var SeoMetadata $seoMetadata */
            $seoMetadata = $event->getData();
            $seoForm     = $event->getForm();
            /** @var TreeNodeInterface $parentModelData */
            $parentModelData = $event->getForm()->getParent()->getData();

            // Title
            if (null === $seoMetadata->getTitle()) {
                $title = $this->title->fromEntity($parentModelData) ?: $this->translator->trans(
                    $this->seoConfig['default_description'],
                    [],
                    $this->seoConfig['translation_domain'],
                    $parentModelData->getLocale()
                );

                $this->setSubFormOption($seoForm, 'title', 'attr', [
                    'placeholder' => html_entity_decode($title),
                ]);
            }
            // Url
            if (null === $seoMetadata->getUrl()) {
                $this->setSubFormOption($seoForm, 'url', 'attr', [
                    'placeholder' => $this->nodeRouter->getPath(
                        $parentModelData,
                        null,
                        false,
                        false,
                        $parentModelData->getLocale()
                    ),
                ]);
            }
            // Description
            if (null === $seoMetadata->getDescription()) {
                $description = $this->excerpt->fromEntity($parentModelData) ?: $this->translator->trans(
                    $this->seoConfig['default_description'],
                    [],
                    $this->seoConfig['translation_domain'],
                    $parentModelData->getLocale()
                );

                $this->setSubFormOption($seoForm, 'description', 'attr', [
                    'placeholder' => html_entity_decode($description),
                ]);
            }
            // Keywords
            if (null === $seoMetadata->getKeywords()) {
                $this->setSubFormOption($seoForm, 'keywords', 'attr', [
                    'placeholder' => $this->seoConfig['default_keywords'],
                ]);
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
