<?php

namespace AppBundle\Controller\Block;

use AppBundle\Entity\Block;
use AppBundle\Entity\Node;
use AppBundle\Service\Business\BlockBusiness;
use AppBundle\Service\Business\NodeBusiness;
use AppBundle\Service\Util\EntityManager\Yarm;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ListingController extends Controller
{
    public function listBlockAction(BlockBusiness $business, Request $request, NodeBusiness $nodeBusiness, Yarm $yarm)
    {
        $addressPorts = $request->request->get('address_ports');
        $nodes = [];
        $excludeCurrent = false;
        $currentNode = $nodeBusiness->getCurrentNode();
        foreach ($addressPorts as $addressPort) {
            $host = $addressPort['host'];
            $port = $addressPort['port'];
            $node = (new Node())->setHost($host)->setPort($port);
            $nodes[] = $node;
            $excludeCurrent = $excludeCurrent || ($currentNode->getPort() == $node->getPort() && $currentNode->getHost() == $node->getHost());

        }
        if(!$excludeCurrent) {
            $blocks = $yarm->getRepository(Block::class)->findAll();
            return $this->json(array_merge($business->getAllBlocks($nodes), [$blocks]));
        }
        return $this->json($business->getAllBlocks($nodes));
    }
}
