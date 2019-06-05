<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class OriginController extends Controller
{
    public function indexAction()
    {
        throw new NotFoundHttpException();
    }

    public function isUpAction()
    {
        return $this->json(true);
    }
}
