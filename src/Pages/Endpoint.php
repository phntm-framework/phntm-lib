<?php

namespace Phntm\Lib\Pages;

use Nyholm\Psr7\Stream;
use Phntm\Lib\Infra\Routing\Attributes\Dynamic;
use Phntm\Lib\Infra\Routing\Attributes\Alias;
use Phntm\Lib\Infra\Routing\Router;
use Phntm\Lib\Infra\Routing\HasResolvableParts;
use Psr\Http\Message\StreamInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

abstract class Endpoint implements EndpointInterface, HasResolvableParts
{
    public static bool $hideFromSitemap = false;
    /**
     * @param array $dynamic_params
     */
    public function __construct(
        protected array $dynamic_params = []
    ) {
    }

    abstract public function __invoke(Request $request): void;

    public function __get(string $name): mixed
    {
        if (array_key_exists($name, $this->dynamic_params)) {
            return $this->dynamic_params[$name];
        }
        return null;
    }

    public function __isset(string $name): bool
    {
        return array_key_exists($name, $this->dynamic_params);
    }

    public function dispatch(Request $request): StreamInterface
    {
        $this($request);
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
            'path' => Router::n2r(static::class),
            'defaults' => [],
            'priority' => Router::calcRoutePriority(Router::n2r(static::class))
        ];

        if (static::isDynamic()) {
            $attribute = $reflection->getAttributes(Dynamic::class)[0]->newInstance();

            $route['path'] = Dynamic::getTypeSafePath($attribute->denoted_namespace);
            $route['priority'] = Router::calcRoutePriority($route['path']);
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
        $this->dynamic_params[$part] = $resolver($this->dynamic_params);
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
}
