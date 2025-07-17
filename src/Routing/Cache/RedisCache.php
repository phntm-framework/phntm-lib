<?php

namespace Phntm\Lib\Routing\Cache;

use Phntm\Lib\Config\Aware\ConfigAwareInterface;
use Phntm\Lib\Config\Aware\ConfigAwareTrait;
use Phntm\Lib\Infra\Debug\Aware\DebugAwareInterface;
use Phntm\Lib\Infra\Debug\Aware\DebugAwareTrait;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Routing\Matcher\Dumper\CompiledUrlMatcherDumper;
use Symfony\Component\Routing\RouteCollection;

class RedisCache implements RouteCacheInterface, DebugAwareInterface, ConfigAwareInterface
{
    use ConfigAwareTrait;
    use DebugAwareTrait;

    public function __construct(
        protected RedisAdapter $cache,
    ) {}

    public function get(): ?array
    {
        $this->debug()->startMeasure('route.cache.get', 'Get Routes from Redis');

        $item = $this->cache->getItem(
            $this->config()->retrieve('routing.cache.key')
        );

        if (!$item->isHit()) {
            $this->debug()->log('cache miss');
            $this->debug()->stopMeasure('route.cache.get');
            return null;
        }

        $routes = $item->get();

        $this->debug()->stopMeasure('route.cache.get');

        return $routes;
    }

    public function set(RouteCollection $routes): bool
    {
        $compiledRoutes = (new CompiledUrlMatcherDumper($routes))->getCompiledRoutes();

        $item = $this->cache->getItem(
            $this->config()->retrieve('routing.cache.key')
        );

        $item->set($compiledRoutes);
        $item->expiresAfter(
            $this->config()->retrieve('routing.cache.ttl')
        );

        return $this->cache->save($item);
    }

    public function clear(): bool
    {
        return true;
    }

}
