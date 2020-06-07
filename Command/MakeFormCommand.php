<?php


namespace EasyApiBundle\Command;

use EasyApiBundle\Util\Maker\EntityGenerator;
use EasyApiBundle\Util\StringUtils\CaseConverter;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Exception\InvalidArgumentException;

final class MakeFormCommand extends AbstractMakerCommand
{
    protected function configure()
    {
        $this
            ->setName(self::$commandPrefix.':form')
            ->setDescription('Generate form for entity')
            ->addArgument(
                'entity_name',
                InputArgument::REQUIRED,
                'Entity name.'
            )
            ->addOption(
                'bundle',
                'bu',
                   InputOption::VALUE_OPTIONAL,
                'The bundle.',
                'AppBundle'
            )
            ->addOption(
                'context',
                'co',
                InputOption::VALUE_OPTIONAL,
                'The context.'
            )
            ->addOption(
                'no-dump',
                'nd',
                InputOption::VALUE_NONE,
                'Ex --no-dump'
            )
        ;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|void|null
     *
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $entityName = $input->getArgument('entity_name');
        $this->validateEntityName($entityName);
        $bundle = $input->getOption('bundle');
        $context = $input->getOption('context');
        $dumpOption = $input->getOption('no-dump');
        $dumpExistingFiles = !$dumpOption;

        // generate form
        $this->generateForm($output, $bundle,  $entityName, $context, $dumpExistingFiles);

    }
}