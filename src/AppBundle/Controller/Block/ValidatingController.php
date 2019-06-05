<?php

namespace AppBundle\Controller\Block;


use AppBundle\Entity\Block;
use AppBundle\Service\Business\BlockBusiness;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ValidatingController extends Controller
{
    public function validateBlockAction(BlockBusiness $blockBusiness, Block $block)
    {
        return $this->json($blockBusiness->isBlockValid($block));
    }

    public function validateBlocksAction(BlockBusiness $blockBusiness)
    {
        return $this->json($blockBusiness->isBlocksValid());
    }
}
