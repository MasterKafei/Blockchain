<?php

namespace AppBundle\Entity;

/**
 * Block
 */
class Block implements \JsonSerializable
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $hash;

    /**
     * @var string
     */
    private $previousHash;

    /**
     * @var \DateTime
     */
    private $creationDate;

    /**
     * @var string
     */
    private $data;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set hash
     *
     * @param string $hash
     *
     * @return Block
     */
    public function setHash($hash)
    {
        $this->hash = $hash;

        return $this;
    }

    /**
     * Get hash
     *
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * Set previousHash
     *
     * @param string $previousHash
     *
     * @return Block
     */
    public function setPreviousHash($previousHash)
    {
        $this->previousHash = $previousHash;

        return $this;
    }

    /**
     * Get previousHash
     *
     * @return string
     */
    public function getPreviousHash()
    {
        return $this->previousHash;
    }

    /**
     * Set creationDate
     *
     * @param \DateTime $creationDate
     *
     * @return Block
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    /**
     * Get creationDate
     *
     * @return \DateTime
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    public function jsonSerialize()
    {
        return array(
            'id' => $this->getId(),
            'previousHash' => $this->getPreviousHash(),
            'hash' => $this->getHash(),
            'data' => $this->getData(),
            'creationDate' => $this->getCreationDate()->getTimestamp(),
        );
    }

    public function getDataToHash()
    {
        return array(
            'previousHash' => $this->getPreviousHash(),
            'data' => $this->getData(),
            'creationDate' => $this->getCreationDate()->getTimestamp(),
        );
    }

    public function deserialize($data)
    {
        $date = new \DateTime();
        $date->setTimestamp($data->creationDate);
        $this->id = intval($data->id);
        $this
            ->setPreviousHash($data->previousHash)
            ->setHash($data->hash)
            ->setData($data->data)
            ->setCreationDate($date);
    }

    /**
     * Set data.
     *
     * @param string $data
     * @return Block
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Get data.
     *
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }
}

