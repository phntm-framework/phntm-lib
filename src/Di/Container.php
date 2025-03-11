<?php

namespace Phntm\Lib\Di;

use Psr\Container\ContainerInterface;
use League\Container\Container as LeagueContainer;
use League\Container\DefinitionContainerInterface;

class Container
{
    private static ?ContainerInterface $container = null;

    public static function get(): DefinitionContainerInterface
    {
        if (self::$container === null) {
            self::$container = new LeagueContainer();
        }

        return self::$container;
    }
}
