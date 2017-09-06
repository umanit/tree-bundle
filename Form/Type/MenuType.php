<?php

namespace Umanit\Bundle\TreeBundle\Form\Type;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MenuType extends AbstractType
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var array
     */
    private $nodeTypes;

    /**
     * @var string
     */
    private $menuEntityClass;

    /**
     * MenuType constructor.
     *
     * @param EntityManagerInterface $em
     * @param array                  $nodeTypes
     * @param string                 $menuEntityClass
     */
    public function __construct(EntityManagerInterface $em, array $nodeTypes, $menuEntityClass)
    {
        $this->em              = $em;
        $this->nodeTypes       = $nodeTypes;
        $this->menuEntityClass = $menuEntityClass;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Build available model nodes
        $models = [];
        foreach ($this->nodeTypes as $nodeType) {
            if ($nodeType['menu'] === true) {
                $explodedFqcn               = explode('\\', $nodeType['class']);
                $models[end($explodedFqcn)] = $nodeType['class'];
            }
        }

        $builder
            ->add('title')
            ->add('position', Choicetype::class, [
                'choices' => [
                    // @todo AGU : set configurable
                    "Principal"    => 'primary',
                    "Pied de page" => 'footer',
                ],
            ])
            ->add('link', LinkType::class, [
                'models'         => $models,
                'allow_external' => true,
                'required'       => false,
            ])
            ->add('parent', EntityType::class, [
                'class'        => $this->menuEntityClass,
                'choices'      => $this->em->getRepository($this->menuEntityClass)->getIndentMenu(),
                'choice_label' => 'title',
                'required'     => false,
            ])
            ->add('save', SubmitType::class, ['attr' => ['class' => 'btn-success']])
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
            'data_class' => $this->menuEntityClass,
        ]);
    }
}
