<?php

namespace Phntm\Lib\Commands\Db;

use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Phntm\Lib\Commands\BaseCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
    name: 'db:tables',
    description: 'Create tables for all models',
)]
class Tables extends BaseCommand
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $sm = $this->getContainer()->get(AbstractSchemaManager::class);

        $tables = $sm->listTableNames();

        foreach ($tables as $table) {
            $output->writeln($table);
        }

        return Command::SUCCESS;
    }
}


