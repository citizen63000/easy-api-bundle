<?php


namespace EasyApiBundle\Command;

use EasyApiBundle\Util\Maker\EntityGenerator;
use EasyApiBundle\Util\StringUtils\CaseConverter;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Exception\InvalidArgumentException;

final class MakeTICommand extends AbstractMakerCommand
{
    protected function configure()
    {
        $this
            ->setName(self::$commandPrefix.':ti')
            ->setDescription('Generate TI for crud')
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
        $dumpOption = $input->getOption('no-dump');
        $dumpExistingFiles = !$dumpOption;

        // generate repository
        $this->generateTi($output, $bundle, null, $entityName, $dumpExistingFiles);
    }
}