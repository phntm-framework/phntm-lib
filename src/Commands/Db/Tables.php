<?php

namespace Phntm\Lib\Commands\Db;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Phntm\Lib\Db\Db;

#[AsCommand(
    name: 'db:tables',
    description: 'Create tables for all models',
)]
class Tables extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $sm = Db::getSchemaManager();

        $tables = $sm->listTableNames();

        foreach ($tables as $table) {
            $output->writeln($table);
        }

        return Command::SUCCESS;
    }
}


