<?php

namespace Phntm\Lib\Pages;

use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\ServerRequestInterface as PsrRequest;
use Symfony\Component\Routing\RouteCollection;

interface EndpointInterface
{
    public function setDynamicParams(array $dynamic_params): void;
    public static function registerRoutes(RouteCollection $routes): void;

    public function dispatch(PsrRequest $request): StreamInterface;
    public function __invoke(): void;

    public function setRequest(PsrRequest $request): void;
    public function getRequest(bool $symfony = false): PsrRequest|SymfonyRequest;
}

