<?php

namespace Phntm\Lib\Pages;

use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Routing\RouteCollection;

interface EndpointInterface
{
    public function setDynamicParams(array $dynamic_params): void;
    public static function registerRoutes(RouteCollection $routes): void;

    public function dispatch(ServerRequestInterface $request): StreamInterface;
    public function __invoke(): void;

    public function setRequest(ServerRequestInterface $request): void;
    public function getRequest(bool $symfony = false): ServerRequestInterface;

    public function getContentType(): string;
}

