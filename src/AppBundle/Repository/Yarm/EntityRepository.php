<?php

namespace AppBundle\Repository\Yarm;

use AppBundle\Service\Util\EntityManager\YarmInterface;

abstract class EntityRepository
{
    private $class;

    private $yarm;

    public function __construct(YarmInterface $yarm, $class)
    {
        $this->class = $class;
        $this->yarm = $yarm;
    }

    public function find($id)
    {
        return $this->findOneBy(['id' => $id]);
    }

    public function findBy($criteria)
    {
        $data = $this->yarm->getData($this->class);
        $entities = [];
        foreach ($data as $id => $datum) {
            $datum = json_decode($datum);
            if(null === $datum) {
                continue;
            }
            foreach ($criteria as $attribut => $value) {
                if ($datum->{$attribut} !== $value) {
                    continue 2;
                }
            }
            $entity = new $this->class();
            $entity->deserialize($datum);
            $entities[] = $entity;
        }

        return $entities;
    }

    public function findOneBy($criteria)
    {
        $entities = $this->findBy($criteria);
        return count($entities) !== 0 ? $entities[0] : null;
    }

    public function findAll()
    {
        return $this->findBy([]);
    }
}
