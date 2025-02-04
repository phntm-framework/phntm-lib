<?php

namespace Phntm\Lib\Infra\Routing;

interface HasResolvableParts
{
    public function resolveDynamicParts(array $dynamic_parts): array;
    public function registerPartResolver(string $part, callable $resolver): void;
}
