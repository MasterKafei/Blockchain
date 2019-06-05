<?php

namespace AppBundle\Service\Util;


class HashAlgorithm
{
    private $hashAlgorithm;

    public function __construct($hashAlgorithm)
    {
        $this->hashAlgorithm = $hashAlgorithm;
    }

    public function getHashAlgorithm()
    {
        return $this->hashAlgorithm;
    }

    public function hash($data)
    {
        return hash($this->getHashAlgorithm(), $data);
    }
}
