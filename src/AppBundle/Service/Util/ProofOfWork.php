<?php

namespace AppBundle\Service\Util;


class ProofOfWork
{
    private $valueNeededInHash;
    private $hashAlgorithmService;

    public function __construct(HashAlgorithm $algorithm, $valueNeededInHash)
    {
        $this->hashAlgorithmService = $algorithm;
        $this->valueNeededInHash = $valueNeededInHash;
    }

    public function isProofOfWorkValid($proofOfWork)
    {
        return 0 === strpos($proofOfWork, $this->valueNeededInHash);
    }

    public function getProofOfWork($data)
    {
        $nonce = 0;
        do {
            $proofOfWork = $this->hashAlgorithmService->hash($data . $nonce);
            ++$nonce;
        } while (!$this->isProofOfWorkValid($proofOfWork));

        return $proofOfWork;
    }
}
