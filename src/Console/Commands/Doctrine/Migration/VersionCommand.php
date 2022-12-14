<?php

namespace TakeruNezu\IntegratingDoctrineWithLaravel\Console\Commands\Doctrine\Migration;

use Doctrine\Migrations\Tools\Console\Command\VersionCommand as DoctrineThisCommand;
use TakeruNezu\IntegratingDoctrineWithLaravel\Console\Commands\Doctrine\DoctrineCommand;
use Doctrine\Migrations\DependencyFactory;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class VersionCommand extends DoctrineCommand
{
    private DoctrineThisCommand $_command;

    public function __construct(DependencyFactory $dependencyFactory)
    {
        parent::__construct();
        $this->_command = new DoctrineThisCommand($dependencyFactory);

        $this
            ->setName('doctrine:' . $this->_command->getDefaultName())
            ->setDescription($this->_command->getDescription())
            ->addArgument(
                'version',
                InputArgument::OPTIONAL,
                'The version to add or delete.',
                null
            )
            ->addOption(
                'add',
                null,
                InputOption::VALUE_NONE,
                'Add the specified version.'
            )
            ->addOption(
                'delete',
                null,
                InputOption::VALUE_NONE,
                'Delete the specified version.'
            )
            ->addOption(
                'all',
                null,
                InputOption::VALUE_NONE,
                'Apply to all the versions.'
            )
            ->addOption(
                'range-from',
                null,
                InputOption::VALUE_OPTIONAL,
                'Apply from specified version.'
            )
            ->addOption(
                'range-to',
                null,
                InputOption::VALUE_OPTIONAL,
                'Apply to specified version.'
            )
            ->setHelp(<<<EOT
The <info>%command.name%</info> command allows you to manually add, delete or synchronize migration versions from the version table:

    <info>%command.full_name% MIGRATION-FQCN --add</info>

If you want to delete a version you can use the <comment>--delete</comment> option:

    <info>%command.full_name% MIGRATION-FQCN --delete</info>

If you want to synchronize by adding or deleting all migration versions available in the version table you can use the <comment>--all</comment> option:

    <info>%command.full_name% --add --all</info>
    <info>%command.full_name% --delete --all</info>

If you want to synchronize by adding or deleting some range of migration versions available in the version table you can use the <comment>--range-from/--range-to</comment> option:

    <info>%command.full_name% --add --range-from=MIGRATION-FQCN --range-to=MIGRATION-FQCN</info>
    <info>%command.full_name% --delete --range-from=MIGRATION-FQCN --range-to=MIGRATION-FQCN</info>

You can also execute this command without a warning message which you need to interact with:

    <info>%command.full_name% --no-interaction</info>
EOT
            );
    }

    public function handle()
    {
        $this->_command->initialize($this->input, $this->output);
        $this->_command->execute($this->input, $this->output);
    }
}