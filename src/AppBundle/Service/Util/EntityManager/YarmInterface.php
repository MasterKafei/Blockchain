<?php

namespace AppBundle\Service\Util\EntityManager;


interface YarmInterface
{
    public function persist($entity);

    public function remove($entity);

    public function getData($class);

    public function getRepository($repository);
}