<?php

namespace AppBundle\Entity;

/**
 * Node
 */
class Node implements \JsonSerializable
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $host;

    /**
     * @var int
     */
    private $port;


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
     * Set host
     *
     * @param string $host
     *
     * @return Node
     */
    public function setHost($host)
    {
        $this->host = $host;

        return $this;
    }

    /**
     * Get host
     *
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Set port
     *
     * @param integer $port
     *
     * @return Node
     */
    public function setPort($port)
    {
        $this->port = intval($port);

        return $this;
    }

    /**
     * Get port
     *
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    public function jsonSerialize()
    {
        return array(
            'id' => $this->id,
            'host' => $this->getHost(),
            'port' => $this->getPort(),
        );
    }

    public function deserialize($data)
    {
        $this->id = $data->id;
        $this
            ->setHost($data->host)
            ->setPort($data->port)
        ;
    }

    public function __toString()
    {
        return $this->host . ':' . $this->port;
    }
}

