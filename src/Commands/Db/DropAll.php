<?php

namespace Phntm\Lib\Commands\Db;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Phntm\Lib\Db\Db;

#[AsCommand(
    name: 'db:drop-all',
    description: 'Drop all tables',
)]
class DropAll extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $sm = Db::getSchemaManager();

            $tables = $sm->listTableNames();

            foreach ($tables as $table) {
                $sm->dropTable($table);
                $output->writeln('Dropped ' . $table);
            }
        } catch (\Exception $e) {
            $output->writeln('Failed: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}


