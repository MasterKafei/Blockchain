<?php

namespace AppBundle\Service\Business;


use AppBundle\Entity\Node;
use AppBundle\Service\Util\EntityManager\Yarm;
use AppBundle\Service\Util\RequestBusiness;
use Symfony\Component\Routing\RouterInterface;

class NodeBusiness
{
    private $requestBusiness;
    private $yarm;
    private $router;
    private $currentNode;

    public function __construct(Yarm $yarm, RequestBusiness $business, RouterInterface $router, $host, $port)
    {
        $this->requestBusiness = $business;
        $this->yarm = $yarm;
        $this->router = $router;
        $this->currentNode = (new Node())->setHost($host)->setPort($port);
    }

    public function isNodeUp(Node $node)
    {
        return boolval($this->requestBusiness->getResponse($node . $this->router->generate('app_is_up')));
    }

    public function checkAllNode()
    {
        $nodes = $this->yarm->getRepository(Node::class)->findAll();
        $validNodes = [];
        /** @var Node $node */
        foreach ($nodes as $node) {
            $index = $node->getHost() . $node->getPort();
            if (!$this->isNodeUp($node) || isset($validNodes[$index])) {
                $this->yarm->remove($node);
            } else {
                $validNodes[$index] = $node;
            }
        }

        return $validNodes;
    }

    public function retrieveAdjacentNode()
    {
        $nodes = $this->yarm->getRepository(Node::class)->findAll();
        foreach ($nodes as $node) {
            $jsonNodes = json_decode(
                    $this->requestBusiness->getResponse($node . $this->router->generate('app_node_listing_list_node')
                )
            );
            foreach ($jsonNodes as $jsonNode) {
                $newNode = new Node();
                $newNode->deserialize($jsonNode);

                if (!$this->nodeExist($newNode)) {
                    $this->yarm->persist($newNode);
                }
            }
        }
    }

    public function nodeExist(Node $node)
    {
        if ($node->getPort() == $this->getCurrentNode()->getPort() && $node->getHost() == $this->getCurrentNode()->getHost()) {
            return true;
        }

        return null !== $this->yarm->getRepository(Node::class)->findOneBy(['host' => $node->getHost(), 'port' => $node->getPort()]);
    }

    public function getCurrentNode()
    {
        return $this->currentNode;
    }

    public function addNode(Node $node)
    {
        $this->yarm->persist($node);
        $this->retrieveAdjacentNode();
        $this->checkAllNode();
    }

    /**
     * Get all nodes.
     *
     * @return Node[]
     */
    public function getAllNodes()
    {
        return $this->yarm->getRepository(Node::class)->findAll();
    }

    public function sendNode(Node $node)
    {
        $currentNode = $this->getCurrentNode();
        $this->requestBusiness->getResponse($node . $this->router->generate('app_node_creation_create_node'), array(
            'host' => $currentNode->getHost(),
            'port' => $currentNode->getPort(),
        ));
    }
}
