<?php

namespace Phntm\Lib\Commands\Db;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Phntm\Lib\Db\Db;

#[AsCommand(
    name: 'db:schema {table}',
    description: 'Show the schema of a table',
)]
class Schema extends Command
{
    protected function configure()
    {
        $this
            ->addArgument('table')
        ;
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $tableName = $input->getArgument('table');

        $sm = Db::getSchemaManager();

        $table = $sm->introspectTable($tableName);

        return Command::SUCCESS;
    }
}
