<?php

namespace Umanit\TreeBundle\Form\Type;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;
use Umanit\TreeBundle\Entity\Node;
use Umanit\TreeBundle\Model\TreeNodeInterface;

class TreeNodeType extends AbstractType
{
    protected ManagerRegistry $doctrine;
    protected TranslatorInterface $translator;

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $doctrine = $this->doctrine;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($doctrine) {
            $object = $event->getForm()->getParent()->getData();

            if (empty($event->getData()) && !empty($object)) {
                $repo = $this->doctrine->getRepository(Node::class);
                $query = $repo->createQueryBuilder('n')
                              ->innerJoin('n.children', 'sub_node')
                              ->where('sub_node.className = :class_name')
                              ->andWhere('sub_node.classId = :class_id')
                              ->orderBy('n.path', 'ASC')
                              ->setParameter(
                                  'class_name',
                                  $this->doctrine->getManager()->getClassMetadata($object::class)->getName()
                              )
                              ->setParameter('class_id', $object->getId())
                              ->getQuery()
                ;
                $entities = $query->getResult();

                $event->setData($entities);
            }
        });
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
            $queryBuilder = null;

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
    public function getParent(): ?string
    {
        return EntityType::class;
    }

    public function setDoctrine(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function setTranslator(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }
}
