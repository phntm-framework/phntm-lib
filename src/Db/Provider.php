<?php

namespace Phntm\Lib\Db;

use Phntm\Lib\Config\Config;
use Phntm\Lib\Di\ModuleProvider;

class Provider extends ModuleProvider
{
    public function provides(string $id): bool
    {
        $services = [
            \Doctrine\DBAL\Connection::class,
            \Doctrine\DBAL\Schema\AbstractSchemaManager::class,
        ];
        
        return in_array($id, $services);
    }

    public function definitions(): void
    {
        $this->getContainer()->addShared(\Doctrine\DBAL\Connection::class, function (Config $config) {
            return \Doctrine\DBAL\DriverManager::getConnection($config->retrieve('db.connection'));
        })->addArgument(Config::class);

        $this->getContainer()->add(\Doctrine\DBAL\Schema\AbstractSchemaManager::class, function () {
            return $this->getContainer()->get(\Doctrine\DBAL\Connection::class)->createSchemaManager();
        });
    }
}
