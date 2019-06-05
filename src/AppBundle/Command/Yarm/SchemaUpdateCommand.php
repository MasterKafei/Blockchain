<?php

namespace AppBundle\Command\Yarm;


use AppBundle\Entity\Block;
use AppBundle\Service\Business\BlockBusiness;
use AppBundle\Service\Util\EntityManager\Yarm;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SchemaUpdateCommand extends Command
{
    private $yarm;
    private $business;

    public function __construct(Yarm $yarm, BlockBusiness $blockBusiness)
    {
        parent::__construct();
        $this->yarm = $yarm;
        $this->business = $blockBusiness;
    }

    public function configure()
    {
        $this
            ->setName('yarm:schema:update')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->yarm->createTables();
    }
}
