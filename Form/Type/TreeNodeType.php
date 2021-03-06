<?php

namespace Umanit\Bundle\TreeBundle\Form\Type;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\CallbackValidator;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormError;
use Umanit\Bundle\TreeBundle\Form\DataTransformer\TreeNodeTransformer;
use Umanit\Bundle\TreeBundle\Model\TreeNodeInterface;

class TreeNodeType extends AbstractType
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
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $doctrine = $this->doctrine;
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($doctrine) {
                $object = $event->getForm()->getParent()->getData();
                if (empty($event->getData()) && !empty($object)) {
                    $repo     = $this->doctrine->getRepository('UmanitTreeBundle:Node');
                    $query    = $repo->createQueryBuilder('n')
                                     ->innerJoin('n.children', 'sub_node')
                                     ->where('sub_node.className = :class_name')
                                     ->andWhere('sub_node.classId = :class_id')
                                     ->orderBy('n.path', 'ASC')
                                     ->setParameter('class_name', $this->doctrine->getManager()->getClassMetadata(get_class($object))->getName())
                                     ->setParameter('class_id', $object->getId())
                                     ->getQuery()
                    ;
                    $entities = $query->getResult();
                    $event->setData($entities);
                }
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options); // TODO: Change the autogenerated stub
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        parent::finishView($view, $form, $options); // TODO: Change the autogenerated stub
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'class'        => 'UmanitTreeBundle:Node',
            'multiple'     => true,
            'choice_label' => 'path',
            'exclude_type' => [],
        ]);

        $resolver->setNormalizer('query_builder', function (OptionsResolver $options, $configs) {
            if (!empty($configs) && ($configs instanceof \Closure) === false) {
                return $configs;
            }

            $er = $options['em']->getRepository($options['class']);
            if (empty($configs)) {
                $queryBuilder = $er
                    ->createQueryBuilder('n')
                    ->where('n.path != :path')
                    ->orderBy('n.path', 'ASC')
                    ->setParameter('path', TreeNodeInterface::ROOT_NODE_PATH)
                ;
            } elseif ($configs instanceof \Closure) {
                $queryBuilder = $configs($er);
            }

            if (!empty($options['exclude_type'])) {
                $andX = $queryBuilder->expr()->andX();
                foreach ($options['exclude_type'] as $index => $excludeType) {
                    $andX->add('n.className != :className'.$index);
                    $queryBuilder->setParameter('className'.$index, $excludeType);
                }
                $queryBuilder->andWhere($andX);
            }

            return $queryBuilder;
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return EntityType::class;
    }

    /**
     * @param Registry $doctrine
     */
    public function setDoctrine($doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * @param Translator $translator
     */
    public function setTranslator($translator)
    {
        $this->translator = $translator;
    }
}
