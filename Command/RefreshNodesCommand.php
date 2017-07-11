<?php

namespace Umanit\Bundle\TreeBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RefreshNodesCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('umanit:tree:refresh');
        $this->setDescription('Refresh all tree nodes');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $nodeHelper = $this->getContainer()->get('umanit.tree.node_helper');

        foreach ($this->getContainer()->getParameter('umanit_tree.controllers_by_class') as $controllers) {
            $output->writeln(sprintf('<info>Refresh %s</info>', $controllers['class']));
            $repository = $this->getContainer()->get('doctrine')->getRepository($controllers['class']);

            $total = $this
                ->getContainer()
                ->get('doctrine')
                ->getManager()
                ->createQueryBuilder()
                ->select('COUNT(t)')
                ->from($controllers['class'], 't')
                ->getQuery()
                ->getSingleScalarResult()
            ;

            $fetchTotal = 0;
            for ($fetchTotal = 0; $fetchTotal < $total; $fetchTotal += 100) {
                $objects = $repository->findBy([], [], 100, $fetchTotal);

                foreach ($objects as $object) {
                    $nodeHelper->updateNodes($object);
                }
            }
        }
    }
}
