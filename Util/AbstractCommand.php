<?php

namespace EasyApiBundle\Util;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class AbstractCommand extends Command
{
    use CoreUtilsTrait;
    protected ?ContainerInterface $container;

    public function __construct(string $name = null, ContainerInterface $container)
    {
        parent::__construct($name);
        $this->container = $container;
    }

    /**
     * Write log with time.
     */
    public function writeLog(OutputInterface $output, string $message, int $option = 0)
    {
        $output->writeln(date('Y-m-d H:i:s', time())." {$message}", $option);
    }

    /**
     * @return object
     */
    protected function get(string $id, int $invalidBehavior = Container::EXCEPTION_ON_INVALID_REFERENCE)
    {
        return $this->getContainer()->get($id, $invalidBehavior);
    }

    /**
     * Shortcut to return the Doctrine Registry service.
     *
     * @return Registry|object
     */
    protected function getDoctrine()
    {
        if (!$this->getContainer()->has('doctrine')) {
            throw new \LogicException('The DoctrineBundle is not registered in your application. Try running "composer require symfony/orm-pack".');
        }

        return $this->getContainer()->get('doctrine');
    }

    /**
     * @return mixed
     */
    public function getParameter(string $name)
    {
        return $this->getContainer()->getParameter($name);
    }
}
