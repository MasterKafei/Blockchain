<?php

namespace AppBundle\Repository\Yarm;


use AppBundle\Entity\Block;

class BlockRepository extends EntityRepository
{
    public function getLastBlock()
    {
        /** @var Block[] $blocks */
        $blocks = $this->findAll();

        foreach ($blocks as $block) {
            foreach ($blocks as $otherBlock) {
                if ($otherBlock->getPreviousHash() === $block->getHash()) {
                    continue 2;
                }
            }

            return $block;
        }

        return null;
    }
}
