<?php

namespace Phntm\Lib\Commands\Db;

use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Phntm\Lib\Commands\BaseCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Phntm\Lib\Db\Db;

#[AsCommand(
    name: 'db:schema {table}',
    description: 'Show the schema of a table',
)]
class Schema extends BaseCommand
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

        $sm = $this->getContainer()->get(AbstractSchemaManager::class);

        $table = $sm->introspectTable($tableName);

        return Command::SUCCESS;
    }
}
