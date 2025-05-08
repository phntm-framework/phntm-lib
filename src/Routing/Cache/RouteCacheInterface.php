<?php

namespace Phntm\Lib\Routing\Cache;

use Symfony\Component\Routing\RouteCollection;

interface RouteCacheInterface
{
    public function get(): array|null;

    public function set(RouteCollection $routes): bool;

    public function clear(): bool;
}
