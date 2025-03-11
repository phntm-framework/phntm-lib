<?php

namespace Phntm\Lib\Db;

use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Phntm\Lib\Config;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Configuration;

class Db
{
    private static bool $isInitialized = false;

    public static Connection $connection;

    public static function init(): void
    {
        if (self::$isInitialized) {
            throw new \Exception('Db already initialized');
        }

        self::$connection = DriverManager::getConnection(
            Config::retrieve('db.connection'),
        );

        self::$isInitialized = true;
    }

    public static function getSchemaManager(): AbstractSchemaManager
    {
        return self::$connection->createSchemaManager();
    }

    public static function getConnection(): Connection
    {
        return self::$connection;
    }
}
