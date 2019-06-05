<?php

namespace AppBundle\Controller\Node;

use AppBundle\Entity\Node;
use AppBundle\Service\Util\EntityManager\Yarm;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ListingController extends Controller
{
    public function listNodeAction(Yarm $yarm)
    {
        return $this->json($yarm->getRepository(Node::class)->findAll());
    }
}
