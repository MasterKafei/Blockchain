<?php

namespace AppBundle\Command;


use AppBundle\Entity\Block;
use AppBundle\Service\Business\BlockBusiness;
use AppBundle\Service\Business\NodeBusiness;
use AppBundle\Service\Util\EntityManager\Yarm;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DownloadChainCommand extends Command
{
    private $yarm;
    private $nodeBusiness;
    private $blockBusiness;

    public function __construct(Yarm $yarm, NodeBusiness $nodeBusiness, BlockBusiness $blockBusiness)
    {
        parent::__construct();
        $this->yarm = $yarm;
        $this->nodeBusiness = $nodeBusiness;
        $this->blockBusiness = $blockBusiness;
    }

    public function configure()
    {
        $this
            ->setName('app:chain:download')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $nodes = array_merge($this->nodeBusiness->getAllNodes(), [$this->nodeBusiness->getCurrentNode()]);
        $chains = [];
        foreach($this->nodeBusiness->getAllNodes() as $node) {
            $chains = array_merge($chains, $this->blockBusiness->getBlocksFromNode($node, $nodes));
        }

        $chain = $this->blockBusiness->getConsensusBlock($chains);
        $blocks = $this->yarm->getRepository(Block::class)->findAll();
        foreach($blocks as $block) {
            $this->yarm->remove($block);
        }
        foreach($chain as $block) {
            $this->yarm->persist($block);
        }
    }
}
