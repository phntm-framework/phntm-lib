<?php

namespace Phntm\Lib\Config;

use Phntm\Lib\Di\ModuleProvider;
use DebugBar\DebugBar;
use Phntm\Lib\Infra\Server;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Contracts\Cache\ItemInterface;

class Provider extends ModuleProvider
{
    public function provides(string $id): bool
    {
        $services = [
            Loader::class,
            Config::class,
        ];
        
        return in_array($id, $services);
    }

    public function definitions(): void
    {
        $this->getContainer()->add(Loader::class);

        $this->getContainer()
            ->addShared(
                Config::class, 
                function (
                    RedisAdapter $cache, Loader $loader, DebugBar $debugBar
                ) {
                    $debugBar->startMeasure('config', 'Loading config');
                    $config = $cache->get('__phntm.config__', function (ItemInterface $item) use ($loader) {
                        $item->expiresAfter(0);

                        $config = new Config();
                        $config->merge($loader->load(ROOT . PHNTM . 'inc/config.php'));
                        $config->merge($loader->load(ROOT . '/' . ltrim(Server::$config, '/')));
                        return $config;
                    });
                    $debugBar->stopMeasure('config');

                    return $config;
                }
            )
            ->addArgument(RedisAdapter::class)
            ->addArgument(Loader::class)
            ->addArgument(DebugBar::class)
        ;
    }
}
