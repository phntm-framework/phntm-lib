<?php

namespace Phntm\Lib\Db;

use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Connection;
use Phntm\Lib\Di\Container;

class Db
{
    private static bool $isInitialized = false;

    public static Connection $connection;

    public static function getSchemaManager(): AbstractSchemaManager
    {
        return Container::get()->get(AbstractSchemaManager::class);
    }

    public static function getConnection(): Connection
    {
        return Container::get()->get(Connection::class);
    }
}
