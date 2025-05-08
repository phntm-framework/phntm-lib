<?php

namespace Phntm\Lib\Cache;

use DebugBar\DataCollector\MessagesCollector;
use League\Container\Argument\Literal\IntegerArgument;
use League\Container\Argument\Literal\StringArgument;
use Phntm\Lib\Config\Config;
use Phntm\Lib\Di\ModuleProvider;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\TypeInfo\TypeResolver\StringTypeResolver;

class Provider extends ModuleProvider
{
    public function provides(string $id): bool
    {
        $services = [
            \Predis\ClientInterface::class,
            RedisAdapter::class,
        ];
        
        return in_array($id, $services);
    }

    public function definitions(): void
    {
        $this->getContainer()->addShared(\Predis\ClientInterface::class, function () {
            return RedisAdapter::createConnection(
                $_ENV['CACHE_DSN'],
                [
                    'class' => \Predis\Client::class,
                ]
            );
        });

        $this->getContainer()->addShared(RedisAdapter::class)
            ->addArgument(\Predis\ClientInterface::class)
            ->addArgument(new StringArgument('phntm'))
            ->addArgument(new IntegerArgument(3600))
            //->addMethodCall('setLogger', [MessagesCollector::class])
        ;
    }
}
