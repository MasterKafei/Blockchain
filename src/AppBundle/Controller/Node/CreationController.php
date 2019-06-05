<?php

namespace AppBundle\Controller\Node;


use AppBundle\Entity\Node;
use AppBundle\Form\Type\Node\CreateNodeType;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;

class CreationController extends Controller
{
    public function createNodeAction(Request $request, KernelInterface $kernel)
    {
        $node = new Node();
        $form = $this->createForm(CreateNodeType::class, $node);
        $form->submit($request->request->all());
        if ($form->isSubmitted() && $form->isValid()) {
            $application = new Application($kernel);
            $application->setAutoExit(false);
            $output = new BufferedOutput();
            $input = new ArrayInput([
                'command' => 'app:node:create',
                'host' => $node->getHost(),
                'port' => $node->getPort(),
                '--dont-send' => true,
            ]);
            $application->run($input, $output);
            $input = new ArrayInput([
                'command' => 'app:chain:download',
            ]);
            $application->run($input, new BufferedOutput());
            return $this->json(true);
        }
        return $this->json($form->getErrors(true));
    }
}
