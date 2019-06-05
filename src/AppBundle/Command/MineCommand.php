<?php

namespace AppBundle\Command;

use AppBundle\Entity\Block;
use AppBundle\Service\Business\BlockBusiness;
use AppBundle\Service\Util\EntityManager\Yarm;
use AppBundle\Service\Util\ProofOfWork;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MineCommand extends Command
{
    /**
     * @var BlockBusiness
     */
    private $blockBusiness;

    /**
     * @var Yarm
     */
    private $registry;

    /**
     * @var ProofOfWork
     */
    private $proofOfWork;

    public function __construct(BlockBusiness $blockBusiness, Yarm $registry, ProofOfWork $proofOfWork)
    {
        $this->blockBusiness = $blockBusiness;
        $this->registry = $registry;
        $this->proofOfWork = $proofOfWork;

        parent::__construct();
    }

    public function configure()
    {
        $this
            ->setName('app:block:mine')
            ->addArgument('data', InputArgument::REQUIRED)
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $block = $this->blockBusiness->getLastBlock();

        $em = $this->registry;
        if(!$block) {
            $block = $this->blockBusiness->getGenesisBlock();
            $em->persist($block);
        }

        $newBlock = new Block();
        $newBlock
            ->setCreationDate(new \DateTime())
            ->setPreviousHash($block->getHash())
            ->setData($input->getArgument('data'))
        ;

        $this->blockBusiness->mineBlock($newBlock);
    }
}
