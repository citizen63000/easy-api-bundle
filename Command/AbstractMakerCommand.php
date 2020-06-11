<?php


namespace EasyApiBundle\Command;

use EasyApiBundle\Util\AbstractCommand;
use EasyApiBundle\Util\Maker\CrudGenerator;
use EasyApiBundle\Util\Maker\FormGenerator;
use EasyApiBundle\Util\Maker\RepositoryGenerator;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractMakerCommand extends AbstractCommand
{
    protected static $commandPrefix = 'api:make';

    /**
     * @param string $name
     */
    protected function validateEntityName(string $name)
    {
        if (preg_match('/[_]+/', $name)) {
            throw new InvalidArgumentException(sprintf('Entity name "%s" is invalid.', $name));
        }
    }

    /**
     * @param OutputInterface $output
     * @param $bundle string
     * @param $context string
     * @param $entityName string
     * @param $dumpExistingFiles boolean
     */
    protected function generateRepository(OutputInterface $output, string $bundle, string $entityName, string $context = null, bool $dumpExistingFiles = false)
    {
        $output->writeln("\n------------- Generate Entity class -------------");
        $generator = new RepositoryGenerator($this->getContainer());
        $filePath = $generator->generate($bundle, $entityName, $context, $dumpExistingFiles);
        $output->writeln("file://$filePath[0] created.");
        $output->writeln("file://$filePath[1] modified.");
    }


    /**
     * @param OutputInterface $output
     * @param $bundle string
     * @param $context string
     * @param $entityName string
     * @param $parent string
     * @param $dumpExistingFiles boolean
     */
    protected function generateForm(OutputInterface $output, string $bundle, string $entityName, $parent = null, string $context = null, bool $dumpExistingFiles = false)
    {
        $output->writeln('------------- Generate Form -------------');
        $generator = new FormGenerator($this->getContainer());
        $filePath = $generator->generate($bundle, $context, $entityName, $parent, $dumpExistingFiles);
        $output->writeln("file://{$filePath} created.\n");
    }

    /**
     * @param OutputInterface $output
     * @param $bundle string
     * @param $context string
     * @param $entityName string
     * @param $parent string
     * @param $dumpExistingFiles boolean
     */
    protected function generateCrud(OutputInterface $output, string $bundle, string $entityName, string $parent = null, $context = null, $dumpExistingFiles = false)
    {
        $output->writeln("\n------------- Generate CRUD -------------");
        $generator = new CrudGenerator($this->getContainer());
        $filesPath = $generator->generate($bundle, $context, $entityName, $parent, $dumpExistingFiles);
        foreach ($filesPath as $type => $file) {
            $type = ucfirst($type);
            $output->writeln("{$type} file://{$file} created.");
        }
    }
//
//    /**
//     * @param OutputInterface $output
//     * @param $bundle string
//     * @param $context string
//     * @param $entityName string
//     * @param $parent string
//     * @param $dumpExistingFiles boolean
//     */
//    protected function generateTI(OutputInterface $output, $bundle, $context, $entityName, $parent, $dumpExistingFiles = false)
//    {
//        $output->writeln("\n------------- Generate TI -------------");
//        $generator = new CrudTIGenerator($this->getContainer());
//        $filesPath = $generator->generate($bundle, $context, $entityName, $parent, $dumpExistingFiles);
//        foreach ($filesPath as $type => $file) {
//            $type = ucfirst($type);
//            $output->writeln("{$type} {$file} created.");
//        }
//    }
}