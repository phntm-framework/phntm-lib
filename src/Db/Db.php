<?php

namespace Phntm\Lib\Db;

use Doctrine\DBAL\Configuration;
use Phntm\Lib\Config;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;

class Db
{
    private static bool $isInitialized = false;

    public static Connection $connection;

    public static Configuration $config;

    public static function init(): void
    {
        if (self::$isInitialized) {
            throw new \Exception('Db already initialized');
        }

        self::$config = ORMSetup::createAttributeMetadataConfiguration(
            paths: Config::get()['db']['entity_paths'],
            isDevMode: true,
        );

        self::$connection = DriverManager::getConnection(
            Config::get()['db']['connection'],
            self::$config,
        );
    }

    public static function getEntityManager(): EntityManager
    {
        return new EntityManager(self::$connection, self::$config);
    }
}
