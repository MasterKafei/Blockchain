<?php

namespace AppBundle\Command\Node;


use AppBundle\Service\Business\NodeBusiness;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CheckCommand extends Command
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
            ->setName('app:node:check')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->nodeBusiness->checkAllNode();
        $this->nodeBusiness->retrieveAdjacentNode();
        $output->writeln("All node checked");
    }
}
