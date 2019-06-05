<?php

namespace AppBundle\Service\Business;

use AppBundle\Entity\Block;
use AppBundle\Entity\Node;
use AppBundle\Service\Util\EntityManager\Yarm;
use AppBundle\Service\Util\HashAlgorithm;
use AppBundle\Service\Util\RequestBusiness;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\RouterInterface;

class BlockBusiness
{
    private $registry;
    private $hashAlgorithmService;
    private $kernel;
    private $nodeBusiness;
    private $requestBusiness;
    private $router;

    public function __construct(Yarm $registry, HashAlgorithm $algorithm, KernelInterface $kernel, NodeBusiness $business, RequestBusiness $requestBusiness, RouterInterface $router)
    {
        $this->registry = $registry;
        $this->hashAlgorithmService = $algorithm;
        $this->kernel = $kernel;
        $this->nodeBusiness = $business;
        $this->requestBusiness = $requestBusiness;
        $this->router = $router;
    }

    public function getNextBlock(Block $previousBlock)
    {
        $block = new Block();
        $block
            ->setCreationDate(new \DateTime())
            ->setPreviousHash($previousBlock->getHash())
            ->setHash($this->mine(
                $block
            ));

        return $block;
    }

    public function mineBlock(Block $block)
    {
        $block->setHash(
            $this->mine($block)
        );


        $this->sendBlock($block, [$this->nodeBusiness->getCurrentNode()]);
        $this->registry->persist($block);

        return $block;
    }

    public function mine(Block $block)
    {
        $data = json_encode($block->getDataToHash());
        return $this->hashAlgorithmService->hash($data);
    }

    public function isBlockValid(Block $block)
    {
        return $this->mine($block) === $block->getHash();
    }

    public function getGenesisBlock()
    {
        $block = new Block();

        return $block
            ->setPreviousHash("0")
            ->setCreationDate((new \DateTime())->setTimestamp(1465154705))
            ->setData("0")
            ->setHash(
                $this->mine($block)
            );
    }

    public function isGenesisBlock(Block $block)
    {
        $genesisBlock = $this->getGenesisBlock();
        return $genesisBlock->getPreviousHash() === $block->getPreviousHash() && $genesisBlock->getCreationDate()->getTimestamp() === $block->getCreationDate()->getTimestamp();
    }

    public function createNextBlockWithoutHash(Block $previousBlock)
    {
        $block = new Block();
        $block
            ->setCreationDate(new \DateTime())
            ->setPreviousHash($previousBlock->getHash());

        return $block;
    }

    public function isBlocksValid()
    {
        $blocks = $this->registry->getRepository(Block::class)->findAll();
        foreach ($blocks as $block) {
            if (!$this->isBlockValid($block)) {
                return false;
            }
        }

        return true;
    }

    public function addBlock(Block $block)
    {
        $application = new Application($this->kernel);
        $input = new ArrayInput([
            'command' => 'app:block:mine',
            'data' => $block->getData(),
        ]);
        $application->run($input, new BufferedOutput());
    }

    public function saveBlock(Block $block)
    {
        $searchBlock = $this->registry->getRepository(Block::class)->findOneBy([
            'previousHash' => $block->getPreviousHash(),
        ]);

        if (null === $searchBlock) {
            $this->registry->persist($block);
            return true;
        }

        return false;
    }

    public function getLastBlock()
    {
        return $this->registry->getRepository(Block::class)->getLastBlock();
    }

    /**
     * @param Node[] $excludedNodes
     * @return Block[][]
     */
    public function getAllBlocks($excludedNodes)
    {
        $nodes = $this->nodeBusiness->getAllNodes();
        $validNodes = [];
        foreach ($nodes as $node) {
            foreach ($excludedNodes as $excludedNode) {
                if ($node->getHost() == $excludedNode->getHost() && $node->getPort() == $excludedNode->getPort()) {
                    continue 2;
                }
            }
            $validNodes[] = $node;
        }
        $blocks = [];
        foreach ($validNodes as $node) {
            $blocks = array_merge($this->getBlocksFromNode($node, array_merge($validNodes, $excludedNodes)), $blocks);
        }

        return $blocks;
    }

    /**
     * Get block from node.
     *
     * @param Node $node
     * @param Node[] $excludedNodes
     * @return Block[]
     */
    public function getBlocksFromNode(Node $node, $excludedNodes)
    {
        $addressPorts = array();
        $excludedNodes[] = $this->nodeBusiness->getCurrentNode();
        foreach ($excludedNodes as $excludedNode) {
            if ($node->getPort() === $excludedNode->getPort() && $node->getHost() === $excludedNode->getHost()) {
                continue;
            }
            $addressPorts[] = array(
                'host' => $excludedNode->getHost(),
                'port' => $excludedNode->getPort(),
            );
        }

        $content = $this->requestBusiness->getResponse($node . $this->router->generate('app_block_listing_list_block'), array(
            'address_ports' => $addressPorts,
        ));

        $jsonBlocks = json_decode($content);
        $blocks = [];

        foreach ($jsonBlocks as $jsonBlock) {
            $temp = [];
            foreach ($jsonBlock as $block) {
                $newBlock = new Block();
                $newBlock->deserialize($block);
                $temp[] = $newBlock;
            }
            $blocks[] = $temp;
        }
        return $blocks;
    }

    /**
     * Get consensus block.
     *
     * @param Block[][] $chains
     * @return Block[]
     */
    public function getConsensusBlock($chains)
    {
        /** @var Block[][] $validateChains */
        $validateChains = [];
        foreach ($chains as $chain) {
            foreach ($chain as $block) {
                if (!$this->isBlockValid($block)) {
                    continue 2;
                }
            }
            $validateChains[] = $chain;
        }

        $blockData = [];
        for ($i = 0; $i < count($validateChains[0]); ++$i) {
            $blocks = [];
            foreach ($validateChains as $chain) {
                if (isset($chain[$i])) {
                    $currentBlock = $chain[$i];
                    $blocks[] = $currentBlock;
                }
            }
            $blockData[] = $blocks;
        }
        $validChains = [];
        $previousBlock = null;
        foreach ($blockData as $blocks) {
            if ($previousBlock) {
                $hash = $previousBlock->getHash();
                for ($i = 0; $i < count($blocks); ++$i) {
                    if ($hash !== $blocks[$i]->getPreviousHash()) {
                        unset($blocks[$i]);
                    }
                }
            }
            $previousBlock = $this->getMostPresentBlock($blocks);
            $validChains[] = $previousBlock;
        }
        return $validChains;
    }

    /**
     * @param Block[] $blocks
     * @return Block
     */
    public function getMostPresentBlock($blocks)
    {
        $dataBlocks = [];
        $max = 0;
        $mostFoundBlock = null;
        foreach ($blocks as $block) {
            $data = $block->getData();
            if (!isset($dataBlocks[$data])) {
                $dataBlocks[$data] = 0;
            }
            $dataBlocks[$data] = $dataBlocks[$data] + 1;
            if ($dataBlocks[$data] > $max) {
                $max = $dataBlocks[$data];
                $mostFoundBlock = $block;
            }
        }

        return $mostFoundBlock;
    }

    /**
     * Send block to every nodes
     *
     * @param Block $block
     * @param Node[] $excludeNodes
     */
    public function sendBlock(Block $block, $excludeNodes)
    {
        if (!$this->isBlockValid($block)) {
            return;
        }

        $excludeNodes[] = $this->nodeBusiness->getCurrentNode();
        $nodes = $this->nodeBusiness->getAllNodes();
        $validNodes = [];
        foreach ($nodes as $node) {
            foreach ($excludeNodes as $excludeNode) {
                if (($node->getHost() == $excludeNode->getHost() && $node->getPort() == $excludeNode->getPort())) {
                    continue 2;
                }
            }
            $validNodes[] = $node;
        }

        foreach ($validNodes as $node) {
            $this->requestBusiness->getResponse($node . $this->router->generate('app_block_creation_receive_block'), [
                'block' => json_encode($block),
                'address_ports' => json_encode(array_merge($validNodes, $excludeNodes)),
            ]);
        }
    }
}
