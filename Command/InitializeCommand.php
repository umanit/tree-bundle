<?php

namespace Umanit\Bundle\TreeBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Umanit\Bundle\TreeBundle\Entity\Node;
use Umanit\Bundle\TreeBundle\Model\TreeNodeInterface;

class InitializeCommand extends ContainerAwareCommand
{
    /**
     * @{inheritDoc}
     */
    protected function configure()
    {
        $this->setName('umanit:tree:initialize');
        $this->setDescription('Initialize the root node');
    }

    /**
     * @{inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $class = $this->getContainer()->getParameter('umanit_tree.root_class');
        $root = new $class();

        $em = $this->getContainer()->get('doctrine')->getManager();
        $em->persist($root);
        $em->flush($root);

        $node = new Node();
        $node->setNodeName(TreeNodeInterface::ROOT_NODE_PATH);
        $node->setClassName(get_class($root));
        $node->setClassId($root->getId());
        $node->setLocale('unknown');

        $em->persist($node);
        $em->flush($node);

        $output->writeln('Initialization finished');
    }
}
