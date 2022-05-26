<?php

namespace EasyApiBundle\Command;

use EasyApiBundle\Util\Maker\EntityGenerator;
use EasyApiBundle\Util\StringUtils\CaseConverter;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Exception\InvalidArgumentException;

final class MakeEntityCommand extends AbstractMakerCommand
{
    protected function configure()
    {
        $this
            ->setName(self::$commandPrefix.':entity')
            ->setDescription('Generate entity from database')
            ->addArgument(
                'entity_name',
                InputArgument::REQUIRED,
                'Entity name.'
            )
            ->addOption(
                'schema',
                'sc',
                InputOption::VALUE_OPTIONAL,
                'Schema name ex `my_schema` or `my_schema`.'
            )
            ->addOption(
                'table_name',
                'ta',
                InputOption::VALUE_OPTIONAL,
                'Table name ex my_table or `my_schema`.`my_table`.'
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
                'parent',
                'pa',
                InputOption::VALUE_OPTIONAL,
                'Ex --parent={AbstractParent}'
            )
            ->addOption(
                'inheritanceType',
                'in',
                InputOption::VALUE_OPTIONAL,
                'Ex --inheritanceType={joined|superclass}'
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

        $tableName = $input->getOption('table_name');
        if(empty($tableName)) {
            $tableName = CaseConverter::convertCamelCaseToSnakeCase($entityName);
        }

        $pieces = explode('.', $tableName);
        if(2 === count($pieces)) {
            $schema = $pieces[0];
            $tableName = $pieces[1];
        } else {
            $schema = $input->getOption('schema');
        }

        $parent = $input->getOption('parent');
        $inheritanceType = $input->getOption('inheritanceType');
        $dumpOption = $input->getOption('no-dump');
        $dumpExistingFiles = !$dumpOption;

        // generate Entity class
        $output->writeln('------------- Generate Entity class -------------');
        $generator = new EntityGenerator($this->getContainer());
        $filePath = $this->getParameter('kernel.project_dir').'/'.$generator->generate($bundle, $tableName, $entityName, $schema, $parent, $inheritanceType, $context, $dumpExistingFiles);
        $output->writeln("file://{$filePath} created.");
    }
}