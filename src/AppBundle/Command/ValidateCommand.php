<?php

namespace AppBundle\Command;

use AppBundle\Entity\Block;
use AppBundle\Service\Business\BlockBusiness;
use AppBundle\Service\Util\EntityManager\Yarm;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ValidateCommand extends Command
{
    private $registry;

    private $blockBusiness;

    public function __construct(Yarm $registry, BlockBusiness $blockBusiness)
    {
        parent::__construct();
        $this->registry = $registry;
        $this->blockBusiness = $blockBusiness;
    }

    public function configure()
    {
        $this->setName('app:chain:validate');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $blocks = $this->registry->getRepository(Block::class)->findAll();
        $progressBar = new ProgressBar($output, count($blocks));
        $progressBar->start();
        $invalidBlock = 0;
        foreach ($blocks as $block) {
            if (!$this->blockBusiness->isBlockValid($block)) {
                ++$invalidBlock;
            }
            $progressBar->advance();
        }

        $progressBar->finish();
        $output->writeln("");
        if($invalidBlock) {
            $output->writeln("$invalidBlock invalid block");
        } else {
            $output->writeln("Every block validated ! You are good to go !");
        }

    }
}
