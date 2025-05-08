<?php

namespace Phntm\Lib\Site\Navigation;

interface ResolverInterface
{
    public function resolve(string $instance): array;
}
