<?php

namespace Umanit\TreeBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Umanit\TreeBundle\Helper\NodeHelper;

class RefreshNodesCommand extends Command
{
    protected static $defaultName = 'umanit:tree:refresh';

    private NodeHelper $nodeHelper;
    private array $nodeTypes;
    private EntityManagerInterface $em;

    public function __construct(NodeHelper $nodeHelper, array $nodeTypes, EntityManagerInterface $em)
    {
        $this->nodeHelper = $nodeHelper;
        $this->nodeTypes = $nodeTypes;
        $this->em = $em;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Refresh all tree nodes');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        foreach ($this->nodeTypes as $controllers) {
            $output->writeln(sprintf('<info>Refresh %s</info>', $controllers['class']));
            $repository = $this->em->getRepository($controllers['class']);

            $total = $this->em
                ->createQueryBuilder()
                ->select('COUNT(t)')
                ->from($controllers['class'], 't')
                ->getQuery()
                ->getSingleScalarResult()
            ;

            for ($fetchTotal = 0; $fetchTotal < $total; $fetchTotal += 100) {
                $objects = $repository->findBy([], [], 100, $fetchTotal);

                foreach ($objects as $object) {
                    $this->nodeHelper->updateNodes($object);
                }
            }
        }

        return Command::SUCCESS;
    }
}
