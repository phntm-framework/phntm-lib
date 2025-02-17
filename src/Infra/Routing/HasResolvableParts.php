<?php

namespace Phntm\Lib\Infra\Routing;

interface HasResolvableParts
{
    public function registerPartResolver(string $part, callable $resolver): void;
}
