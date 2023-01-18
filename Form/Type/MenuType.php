<?php

namespace Umanit\TreeBundle\Form\Type;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MenuType extends AbstractType
{
    public function __construct(
        private EntityManagerInterface $em,
        private array $nodeTypes,
        private $menuEntityClass,
        private array $menus
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Build available model nodes
        $models = [];

        foreach ($this->nodeTypes as $nodeType) {
            if ($nodeType['menu'] === true) {
                $explodedFqcn = explode('\\', $nodeType['class']);
                $models[end($explodedFqcn)] = $nodeType['class'];
            }
        }

        $builder
            ->add('title')
            ->add('position', Choicetype::class, [
                'choices' => array_combine($this->menus, $this->menus),
            ])
            ->add('link', LinkType::class, [
                'models'         => $models,
                'allow_external' => true,
                'required'       => false,
            ])
            ->add('parent', EntityType::class, [
                'class'        => $this->menuEntityClass,
                'choices'      => $this->em->getRepository($this->menuEntityClass)->getIndentMenu(
                    $options['menu_position']
                ),
                'choice_label' => 'title',
                'required'     => false,
            ])
            ->add('updatedAt', null, [
                'data'  => new \DateTimeImmutable(),
                'label' => false,
                'attr'  => [
                    'style' => 'display:none;',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'    => $this->menuEntityClass,
            'menu_position' => 'primary',
        ]);
    }
}
