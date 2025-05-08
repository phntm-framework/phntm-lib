<?php

namespace Phntm\Lib\Routing;

use Phntm\Lib\Di\ModuleProvider;
use Phntm\Lib\Infra\Routing\Router;
use Phntm\Lib\Routing\Cache\RouteCacheInterface;
use Symfony\Component\Cache\Adapter\RedisAdapter;

class Provider extends ModuleProvider
{
    public function provides(string $id): bool
    {
        $services = [
            \Phntm\Lib\Routing\RouterInterface::class,
            \Phntm\Lib\Routing\Cache\RouteCacheInterface::class,
        ];

        return in_array($id, $services);
    }

    public function definitions(): void
    {
        $this->getContainer()
            ->add(\Phntm\Lib\Routing\RouterInterface::class, DirectoryRouter::class)
            ->addArgument(Cache\RedisCache::class)
            ->addArgument(RedisAdapter::class)
        ;
    }   
}
