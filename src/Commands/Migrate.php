<?php

namespace Phntm\Lib\Commands;

use Phntm\Lib\Config;
use Phntm\Lib\Db\Db;
use Phntm\Lib\Config\Config as PhntmConfig;
use Phntm\Lib\Di\Container;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
    name: 'db:migrate',
    description: 'Migrate the database',
)]
class Migrate extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $container = Container::get();
        $config = $container->get(PhntmConfig::class);
        $modelPaths = $config->retrieve('db.models');
        dump($modelPaths);
        // find 
        foreach ($modelPaths as $path => $namespace) {
            $files = glob(ROOT . $path . '*.php');
            dump($path);
            dump($files);

            foreach ($files as $file) {
                $model = $namespace . '\\' . pathinfo($file, PATHINFO_FILENAME);

                dump($model);
                // if the class does not exist, skip it
                if (!class_exists($model)) {
                    continue;
                }
                // if the class is not a model, skip it
                if (!is_subclass_of($model, \Phntm\Lib\Model::class)) {
                    continue;
                }
                /** @var \Phntm\Lib\Model $model */

                try {
                    $this->handleMigration($model, $output);

                } catch (\Exception $e) {
                    $output->write(' - Failed: ' . $e->getMessage(), true);
                    continue;
                }

                $output->writeln('');
            }
        }
        return Command::SUCCESS;
    }

    protected function handleMigration(string $model, OutputInterface $output): void
    {
        /** @var \Phntm\Lib\Model $model */
        $output->write($model::getTableName() . ' ');
        if (!$model::tableExists()) {
            $model::createTable();

            $output->writeln('Created');
            return;
        }
        
        $output->write('Exists');

        $sm = Container::get()->get(\Doctrine\DBAL\Schema\AbstractSchemaManager::class);

        $comparator = $sm->createComparator();

        $diff = $comparator->compareTables(
            $model::getExistingSchema(),
            $model::getCurrentSchema()
        );

        if ($diff->isEmpty()) {
            $output->write(' - No changes');
            return;
        }

        $sm->alterTable($diff);
        $output->write(' - Altered');

        return;
    }
}
