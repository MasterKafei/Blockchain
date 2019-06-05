<?php

namespace AppBundle\Command\Node;

use AppBundle\Entity\Node;
use AppBundle\Service\Business\NodeBusiness;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CreateCommand extends Command
{
    private $nodeBusiness;

    public function __construct(NodeBusiness $business)
    {
        parent::__construct();
        $this->nodeBusiness = $business;
    }

    public function configure()
    {
        $this
            ->setName('app:node:create')
            ->addArgument('host', InputArgument::REQUIRED)
            ->addArgument('port', InputArgument::REQUIRED)
            ->addOption('dont-send', null, InputOption::VALUE_NONE);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $node = (new Node())
            ->setHost($input->getArgument('host'))
            ->setPort($input->getArgument('port'));

        if ($this->nodeBusiness->nodeExist($node)) {
            $output->writeln("Node already present");
            return;
        }

        $this->nodeBusiness->addNode($node);
        if (!$input->getOption('dont-send')) {
            $this->nodeBusiness->sendNode($node);
        }
    }
}
