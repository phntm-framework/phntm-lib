<?php

namespace Phntm\Lib\Pages;

use Symfony\Component\HttpFoundation\Request;
use Psr\Http\Message\StreamInterface;
use Symfony\Component\Routing\RouteCollection;

interface PageInterface
{
    public function __invoke(Request $request): void;
    public static function registerRoutes(RouteCollection $routes): void;
}
