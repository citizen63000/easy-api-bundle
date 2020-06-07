<?php


namespace EasyApiBundle\Util;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

abstract class AbstractCommand extends ContainerAwareCommand
{
    use CoreUtilsTrait;

    /**
     * Write log with time.
     *
     * @param OutputInterface $output
     * @param string          $message
     * @param int             $option
     */
    public function writeLog(OutputInterface $output, string $message, int $option = 0)
    {
        $output->writeln(date('Y-m-d H:i:s', time())." {$message}", $option);
    }

    /**
     * @param string $id
     * @param int    $invalidBehavior
     *
     * @return object
     */
    protected function get(string $id, int $invalidBehavior = Container::EXCEPTION_ON_INVALID_REFERENCE)
    {
        return $this->getContainer()->get($id, $invalidBehavior);
    }

    /**
     * Shortcut to return the Doctrine Registry service.
     *
     * @return \Doctrine\Bundle\DoctrineBundle\Registry|object
     */
    protected function getDoctrine()
    {
        if (!$this->getContainer()->has('doctrine')) {
            throw new \LogicException('The DoctrineBundle is not registered in your application. Try running "composer require symfony/orm-pack".');
        }

        return $this->getContainer()->get('doctrine');
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function getParameter(string $name)
    {
        return $this->getContainer()->getParameter($name);
    }

}