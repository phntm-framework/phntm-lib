<?php

namespace Phntm\Lib\Pages;

use League\Container\ContainerAwareInterface;
use League\Container\ContainerAwareTrait;
use Nyholm\Psr7\Stream;
use Phntm\Lib\Infra\Debug\Aware\DebugAwareInterface;
use Phntm\Lib\Infra\Debug\Aware\DebugAwareTrait;
use Phntm\Lib\Infra\Routing\Attributes\Dynamic;
use Phntm\Lib\Infra\Routing\Attributes\Alias;
use Phntm\Lib\Routing\UtilsTrait as RoutingUtils;
use Phntm\Lib\Infra\Routing\HasResolvableParts;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

abstract class Endpoint implements 
    RequestHandlerInterface,
    EndpointInterface, 
    HasResolvableParts, 
    ContainerAwareInterface,
    DebugAwareInterface
{
    use ContainerAwareTrait;
    use DebugAwareTrait;
    use RoutingUtils;

    public static bool $hideFromSitemap = false;

    private ServerRequestInterface $request;

    protected array $dynamic_params = [];

    protected array $part_resolvers = [];

    public function setDynamicParams(array $dynamic_params): void
    {
        $this->dynamic_params = array_merge($dynamic_params, $this->dynamic_params);
    }

    abstract public function __invoke(): void;

    public function __get(string $name): mixed
    {
        if (array_key_exists($name, $this->part_resolvers)) {
            $this->dynamic_params[$name] = $this->part_resolvers[$name]();
        }

        if (array_key_exists($name, $this->dynamic_params)) {
            return $this->dynamic_params[$name];
        }

        return null;
    }

    public function __call(string $name, array $arguments): mixed
    {
        if (array_key_exists($name, $this->part_resolvers)) {
            return $this->part_resolvers[$name]();
        }

        return null;
    }

    public function __isset(string $name): bool
    {
        return array_key_exists($name, $this->dynamic_params);
    }

    public function dispatch(ServerRequestInterface $request): StreamInterface
    {
        $this->setRequest($request);

        $this();
        // this will never be called, the Endpoint class is only for redirects etc
        return Stream::create('');
    }

    public static function registerRoutes(RouteCollection $routes): void
    {
        $route = static::resolveBaseRoute();
        $routes->add($route['name'], new Route($route['path'], $route['defaults']), $route['priority']);
    }

    protected static function resolveBaseRoute(): array
    {
        $reflection = new \ReflectionClass(static::class);

        $route = [
            'name' => static::class,
            'path' => static::n2r(static::class),
            'defaults' => [],
            'priority' => static::calcRoutePriority(static::n2r(static::class))
        ];

        if (static::isDynamic()) {
            $attribute = $reflection->getAttributes(Dynamic::class)[0]->newInstance();

            $route['path'] = Dynamic::getTypeSafePath($attribute->denoted_namespace);
            $route['priority'] = static::calcRoutePriority($route['path']);
            $route['defaults'] = $attribute->defaults;
        } 

        if ($reflection->getAttributes(Alias::class)) {
            $route['path'] = $reflection->getAttributes(Alias::class)[0]->newInstance()->alias;
        }

        return $route;
    }

    // TODO search through parents for Dynamic attribute
    public static function isDynamic(): Dynamic|false
    {
        $reflection = new \ReflectionClass(static::class);
        if (!$reflection->getAttributes(Dynamic::class)) {
            return false;
        }
        return $reflection->getAttributes(Dynamic::class)[0]->newInstance();
    }


    public function registerPartResolver(string $part, callable $resolver): void
    {
        $this->part_resolvers[$part] = function () use ($resolver) {
            return $resolver($this->dynamic_params);
        };
    }

    public static function url(array $params = []): string
    {
        $route = static::resolveBaseRoute();
        $url = $route['path'];

        foreach ($params as $key => $value) {
            $url = str_replace('{' . $key . '}', $value, $url);
        }

        return $url;
    }

    public function setRequest(ServerRequestInterface $request): void
    {
        $this->request = $request;
    }

    public function getRequest(bool $symfony = false): ServerRequestInterface
    {
        return $this->request;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->setRequest($request);
        return $this->dispatch($request);
    }
}
