<?php

namespace Umanit\Bundle\TreeBundle\Form\Type;

use AppBundle\Entity\Actuality;
use AppBundle\Entity\Assistance;
use AppBundle\Entity\CategoryActuality;
use AppBundle\Entity\ContactPage;
use AppBundle\Entity\FaqPage;
use AppBundle\Entity\Page;
use Umanit\Bundle\TreeBundle\Entity\Menu;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

class MenuType extends AbstractType
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * MenuType constructor.
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add(
                'locale',
                Choicetype::class,
                [
                    'choices'  => [
                        // @TODO : list languages, add undefined
                        "anglais" => 'en',
                        "français" => 'fr',
                    ]
                ]
            )
            ->add(
                'imageFile',
                VichImageType::class,
                [
                    'label'        => 'Image',
                    'required'     => false,
                    'allow_delete' => true,
                    'attr'         => [
                        'imagine_pattern' => 'admin',
                    ],
                ]
            )
            ->add('altImage')
            ->add(
                'position',
                Choicetype::class,
                [
                    'choices'  => [
                        "Principal" => 'primary',
                        "Pied de page" => 'footer',
                    ]
                ]
            )
            ->add(
                'link',
                LinkType::class,
                array(
                    'label' => 'Lien',
                    // @TODO : list every classes implementing TreeNodeInterface
                    'models' => array(
                        'Page' => Page::class,
                        'Assistant de recherche' => Assistance::class,
                        'Actualités' => Actuality::class,
                        'Catégorie d\'actualitée' => CategoryActuality::class,
                        'FAQ' => FaqPage::class,
                        'Contact' => ContactPage::class,
                    ),
                    'allow_external' => true,
                    'required' => false,
                )
            )
            ->add(
                'parent',
                EntityType::class,
               [
                   'class' => Menu::class,
                   'choices' => $this->em->getRepository(Menu::class)->getIndentMenu(),
                   'choice_label' => 'title',
                   'required' => false,
                ]
            )
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
        $resolver->setDefaults(array(
            'data_class' => Menu::class,
        ));
    }
}
