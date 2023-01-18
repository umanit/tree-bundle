<?php

namespace Umanit\TreeBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Umanit\TreeBundle\Entity\Node;
use Umanit\TreeBundle\Model\TreeNodeInterface;

class InitializeCommand extends Command
{
    protected static $defaultName = 'umanit:tree:initialize';

    private ?string $rootClass;
    private EntityManagerInterface $em;

    public function __construct(?string $rootClass, EntityManagerInterface $em)
    {
        $this->rootClass = $rootClass;
        $this->em = $em;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Initialize the root node');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $class = $this->rootClass;
        $root = new $class();

        $this->em->persist($root);
        $this->em->flush($root);

        $node = new Node();

        $node->setNodeName(TreeNodeInterface::ROOT_NODE_PATH);
        $node->setClassName(get_class($root));
        $node->setClassId($root->getId());
        $node->setLocale('unknown');

        $this->em->persist($node);
        $this->em->flush($node);

        $output->writeln('Initialization finished');

        return Command::SUCCESS;
    }
}
