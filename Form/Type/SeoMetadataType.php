<?php

namespace Umanit\TreeBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;
use Umanit\TreeBundle\Entity\SeoMetadata;
use Umanit\TreeBundle\Helper\Excerpt;
use Umanit\TreeBundle\Helper\Title;
use Umanit\TreeBundle\Model\TreeNodeInterface;
use Umanit\TreeBundle\Router\NodeRouter;

class SeoMetadataType extends AbstractType
{
    public function __construct(
        private NodeRouter $nodeRouter,
        private Excerpt $excerpt,
        private Title $title,
        private TranslatorInterface $translator,
        private array $seoConfig
    ) {
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
            $seoForm = $event->getForm();
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
     */
    protected function setSubFormOption(
        FormInterface $parentForm,
        string $childName,
        string $optionName,
        mixed $optionValue
    ) {
        $options = $parentForm->get($childName)->getConfig()->getOptions();
        $options[$optionName] = $optionValue;

        $parentForm->add(
            $childName,
            $parentForm->get($childName)->getConfig()->getType()->getInnerType()::class,
            $options
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SeoMetadata::class,
            'translation_domain' => 'UmanitTreeBundle',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'umanit_seo_metadata_type';
    }
}
