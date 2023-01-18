<?php

namespace Umanit\TreeBundle\Form\Type;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Umanit\TreeBundle\Entity\Link;
use Umanit\TreeBundle\Model\TreeNodeInterface;

class LinkType extends AbstractType
{
    public function __construct(protected Registry $doctrine, protected Translator $translator)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Form builder
        if (!$options['allow_internal'] && !$options['allow_external']) {
            throw new \InvalidArgumentException(
                'You must allow at least internal or external link on umanit_link_type'
            );
        }

        if ($options['allow_external']) {
            $builder->add('externalLink', TextType::class, [
                'translation_domain' => $options['translation_domain'],
                'label'              => $options['label_external'],
                'required'           => false,
            ]);
        }

        if ($options['allow_internal']) {
            $data = [];

            foreach ($options['models'] as $displayName => $classPath) {
                $repo = $this->doctrine->getRepository($classPath);
                $filters = $options['query_filters'][$classPath] ?? [];
                $entities = $repo->findBy($filters);

                $data[$displayName] = [];

                foreach ($entities as $entity) {
                    $clazz = $this->doctrine->getManager()->getClassMetadata($entity::class)->getName();
                    $data[$displayName][$entity->__toString().($entity->getLocale() !== TreeNodeInterface::UNKNOWN_LOCALE ?
                        ' ('.$entity->getLocale().')' :
                        '')] = $entity->getId().';'.$clazz;
                }
            }

            $builder
                ->add('internalLink', ChoiceType::class, [
                    'label'              => $options['label_internal'],
                    'translation_domain' => $options['translation_domain'],
                    'choices'            => $data,
                    'attr'               => ['class' => 'umanit-form-select2'],
                    'required'           => false,
                ])
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
        $resolver->setDefaults([
            'data_class'         => Link::class,
            'models'             => [],
            'query_filters'      => [],
            'allow_internal'     => true,
            'allow_external'     => true,
            'translation_domain' => 'UmanitTreeBundle',
            'label_internal'     => 'link.internal',
            'label_external'     => 'link.external',
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'umanit_link_type';
    }
}
