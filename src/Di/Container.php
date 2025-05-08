<?php

namespace Phntm\Lib\Di;

use DebugBar\DebugBar;
use Doctrine\DBAL\Connection;
use League\Container\ContainerAwareInterface;
use League\Container\ReflectionContainer;
use Phntm\Lib\Config\Aware\ConfigAwareInterface;
use Phntm\Lib\Config\Config;
use Phntm\Lib\Db\Aware\ConnectionAwareInterface;
use Phntm\Lib\Infra\Debug\Aware\DebugAwareInterface;
use Psr\Container\ContainerInterface;
use League\Container\Container as LeagueContainer;
use League\Container\DefinitionContainerInterface;

class Container
{
    private static ?ContainerInterface $container = null;

    public static function get(): DefinitionContainerInterface
    {
        if (self::$container === null) {
            $container = new LeagueContainer();
            $container->delegate(new ReflectionContainer(true));

            $container->inflector(ContainerAwareInterface::class)
                ->invokeMethod('setContainer', [ContainerInterface::class])
            ;

            $container->inflector(DebugAwareInterface::class)
                ->invokeMethod('setDebugBar', [DebugBar::class])
            ;

            $container->inflector(ConnectionAwareInterface::class)
                ->invokeMethod('setConnection', [Connection::class])
            ;

            $container->inflector(ConfigAwareInterface::class)
                ->invokeMethod('setConfig', [Config::class])
            ;

            // Register the container itself
            $container->addShared(ContainerInterface::class, $container);

            self::$container = $container;
        }

        return self::$container;
    }
}
