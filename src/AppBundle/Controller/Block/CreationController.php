<?php

namespace AppBundle\Controller\Block;

use AppBundle\Entity\Block;
use AppBundle\Entity\Node;
use AppBundle\Service\Business\BlockBusiness;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class CreationController extends Controller
{
    public function createBlockAction(BlockBusiness $blockBusiness, Request $request)
    {
        $data = $request->request->get('data');

        if (!$data) {
            return $this->json("data parameters not send");
        }

        $block = new Block();
        $block
            ->setData($data);

        $blockBusiness->addBlock($block);

        return $this->json("Registered");
    }

    public function receiveBlockAction(BlockBusiness $blockBusiness, Request $request)
    {
        $jsonBlock = $request->request->get('block');
        $jsonNode = $request->request->get('address_ports');

        $block = new Block();
        $block->deserialize(json_decode($jsonBlock));

        if (!$blockBusiness->isBlockValid($block)) {
            return $this->json("invalid block");
        }
        $nodes = [];
        foreach (json_decode($jsonNode) as $jsonNode) {
            $node = (new Node());
            $node->deserialize($jsonNode);
            $nodes[] = $node;
        }

        if($blockBusiness->saveBlock($block)) {
            $blockBusiness->sendBlock($block, $nodes);
        }

        return $this->json(true);
    }
}
