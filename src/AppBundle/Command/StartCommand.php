<?php

namespace AppBundle\Command;


use AppBundle\Entity\Block;
use AppBundle\Service\Business\BlockBusiness;
use AppBundle\Service\Util\EntityManager\Yarm;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class StartCommand extends Command
{
    private $host;
    private $port;
    private $container;
    private $kernel;
    private $mainNode;
    private $yarm;
    private $blockBusiness;

    public function __construct(KernelInterface $kernel, ContainerInterface $container, Yarm $yarm, BlockBusiness $blockBusiness, $host, $port, $mainNode)
    {
        parent::__construct();
        $this->host = $host;
        $this->port = $port;
        $this->kernel = $kernel;
        $this->container = $container;
        $this->mainNode = $mainNode;
        $this->yarm = $yarm;
        $this->blockBusiness = $blockBusiness;
    }

    public function configure()
    {
        $this
            ->setName('app:run');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $application = new Application($this->kernel);
        $application->setAutoExit(false);

        if(count($this->yarm->getRepository(Block::class)->findAll()) === 0) {
            $this->yarm->persist($this->blockBusiness->getGenesisBlock());
        }

        if (!($this->mainNode['host'] == $this->host && $this->mainNode['port'] == $this->port)) {
            $input = new ArrayInput([
                'command' => 'app:node:create',
                'host' => $this->mainNode['host'],
                'port' => $this->mainNode['port'],
            ]);
            $application->run($input, $output);
        }

        $input = new ArrayInput([
            'command' => 'app:node:check'
        ]);
        $application->run($input, $output);

        $input = new ArrayInput([
            'command' => 'app:chain:download',
        ]);
        $application->run($input, $output);

        $input = new ArrayInput([
            'command' => 'app:chain:validate',
        ]);
        $application->run($input, $output);

        $input = new ArrayInput([
            'command' => 'server:run',
            'addressport' => $this->host . ':' . $this->port,
        ]);
        $application->run($input, $output);
    }
}
