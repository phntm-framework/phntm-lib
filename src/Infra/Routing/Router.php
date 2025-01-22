<?php

namespace Phntm\Lib\Infra\Routing;

use Phntm\Lib\Infra\Debug\Debugger;
use Bchubbweb\PhntmFramework\Pages\PageInterface;
use Bchubbweb\PhntmFramework\Pages\Sitemap\Page as Sitemap;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Matcher\Dumper\CompiledUrlMatcherDumper;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\Matcher\CompiledUrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;
use ReflectionClass;

/**
 * Handles routing and pages
 *
 * gathers autoloaded classes from composer and checks route matches against 
 * existing namespaces
 */
class Router
{
    const CACHE_FILE = ROOT . '/tmp/cache/routes.php';

    public RouteCollection $routes;

    private UrlMatcher $matcher;

    public ?string $notFound = null;

    public function __construct(protected Request $request)
    {

        $context = (new RequestContext())->fromRequest($this->request);

        if (file_exists(self::CACHE_FILE) && !isLocal()) {

            $compiledRoutes = $this->getCachedRoutes();

            $this->matcher = new CompiledUrlMatcher($compiledRoutes, $context);

        } else {
            Debugger::getBar()['time']->startMeasure('Router', 'Index Routes');
            $this->indexRoutes();
            Debugger::getBar()['time']->stopMeasure('Router');

            $this->matcher = new UrlMatcher($this->routes, $context);
            if (!isLocal()) {
                $this->cacheRoutes();
            }
        }
    }

    /**
     * Gathers all Pages\\ routes from autoloaded classes and adds them to the 
     * RouteCollection
     *
     * parses Dynamic attributes to gather route variables and their types
     */
    public function indexRoutes(): void
    {
        $this->routes = new RouteCollection();

        $classes = $this->autoload();

        foreach ($classes as $pageClass => $path) {

            $reflection = new ReflectionClass($pageClass);
            if ($reflection->getAttributes('Bchubbweb\PhntmFramework\Router\NotFound')) {
                $this->notFound = $pageClass;
                continue;
            }
            if ($reflection->getAttributes('Bchubbweb\PhntmFramework\Router\Dynamic')) {
                $denoted_namespace = $reflection->getAttributes('Bchubbweb\PhntmFramework\Router\Dynamic')[0]->getArguments()[0];

                $parts = explode('\\', $denoted_namespace);

                $variables = array_filter($parts, function(string $part) {
                    return (strpos($part, '{') === 0 && strpos($part, '}') === strlen($part) - 1);
                });
                $variables = array_map(function(string $part) {
                    return substr($part, 1, strlen($part) - 2);
                }, $variables);

                $mapped_variables = [];

                foreach ($variables as $variable) {
                    $default = '';
                    if (strpos($variable, ':') !== false) {
                        [$type, $variable] = explode(':', $variable);
                        $default = match($type) {
                            'int' => -1,
                            'string' => '',
                            'float' => 0.0,
                            'bool' => false,
                            'array' => [],
                            default => '',
                        };
                    }
                    $mapped_variables[$variable] = $default;
                }

                $typesafe_parts = array_map(function(string $part) {
                    $type_separator = strpos($part, ':');
                    if ($type_separator !== false) {

                        $type = explode(':', $part)[0];

                        $part = preg_replace('/{(\w+):([^}]+)}/', '{$2}', $part);
                        $part = rtrim($part, '}');

                        // determine the regex for the type
                        $regex = match(ltrim(trim($type), '{')) {
                            'int' => '[1-9][0-9]*',
                            'string' => '[A-Za-z0-9]+(?:-[A-Za-z0-9]+)*',
                            'float' => '\d+\.\d+',
                            'bool' => 'true|false|1|0|yes|no',
                            'array' => '\w+',
                        };
                         
                        if (!$regex) {
                            return;
                        }
                        $part .= "<$regex>}";
                    };
                    return $part;
                }, $parts);
                
                $typesafe_namespace = implode('\\', $typesafe_parts);

                $this->routes->add($pageClass, new Route(self::n2r($typesafe_namespace), $mapped_variables), 2);
                continue;
            }

            $this->routes->add($pageClass, new Route(self::n2r($pageClass)), 4);
        }
        $this->routes->add(Sitemap::class, new Route('/sitemap.xml'), 4);
    }

    /**
     * Dispatches a route from a given request and returns a page or status code
     *
     * @returns PageInterface | int
     */
    public function dispatch(): PageInterface | int
    {
        Debugger::getBar()['time']->startMeasure('Router', 'Dispatch Route');
        try {
            $attributes = $this->matcher->match($this->request->getPathInfo());

            if (!class_exists($attributes['_route'])) {
                throw new \Symfony\Component\Routing\Exception\ResourceNotFoundException('Page not found');
            }

            $route = $attributes['_route'];
            unset($attributes['_route']);

            $reflection = new ReflectionClass($route);

            // handle request method restrictions
            if ($reflection->getAttributes('Bchubbweb\PhntmFramework\Router\Method')) {

                $arguments = $reflection->getAttributes('Bchubbweb\PhntmFramework\Router\Method')[0]->getArguments();

                $methods = $arguments[0];
                $allow = isset($arguments[1]) ? $arguments[1] : true;

                $matches = in_array($this->request->getMethod(), $methods);

                if ($matches !== $allow) {
                    return 405;
                }
            }

            /** @var Bchubbweb\PhntmFramework\Pages\AbstractPage $page */
            $page = new $route($attributes);

            return $page;

        } catch (\Symfony\Component\Routing\Exception\ResourceNotFoundException $exception) {

            // if matcher fails
            return $this->notFound ? new $this->notFound : 404;
        } catch (\Exception $exception) {

            // if any error occurs
            return 500;
        } finally {
            Debugger::getBar()['time']->stopMeasure('Router');
        }
    }

    /**
     * Autoloads the classes from composer or cache
     *
     * @returns array<string>
     */
    protected function autoload(): array
    {
        $res = get_declared_classes();
        $autoloaderClassName = '';
        foreach ( $res as $className) {
            if (strpos($className, 'ComposerAutoloaderInit') === 0) {
                $autoloaderClassName = $className;
                break;
            }
        }
        $classLoader = $autoloaderClassName::getLoader();
        $classes = $classLoader->getClassMap();

        $classes = array_filter($classes, function($key) {
            return (strpos($key, "Pages\\") === 0);
        }, ARRAY_FILTER_USE_KEY);

        return $classes;
    }

    /**
     * Converts a namespace to a route
     *
     * @returns string
     */
    public static function n2r(string $namespace): string
    {
        // remove the namespace and the class name suffix
        $namespace = preg_replace('/\\\Page$/', '', $namespace);
        $namespace = ltrim($namespace, 'Pages');

        $namespace = explode('\\', $namespace);
        $namespace = implode('/', array_map('lcfirst', $namespace));
        return $namespace;
    }

    /**
     * Converts a route to a namespace
     *
     * @returns string
     */
    public static function r2n(string $route): string
    {
        $route = explode('/', $route);
        $route = implode('\\', array_map('ucfirst', $route));
        return 'Pages' . $route . '\\Page';
    }

    /**
     * Converts a route to a path path in the pages folder
     *
     * @returns string
     */
    public static function r2p(string $route): string
    {
        $route = explode('/', $route);
        $route = implode('/', array_map('ucfirst', $route));
        return $route;
    }


    /**
     * Caches the routes and saves them to a file
     *
     * @returns void
     */
    private function cacheRoutes(): bool
    {
        $compiledRoutes = (new CompiledUrlMatcherDumper($this->routes))->getCompiledRoutes();

        $fileDeleted = false;

        if (!($filePath = tempnam(ROOT . "/tmp/cache", "temp-phntm-routes-"))) {
            return false;
        }

        try {
            if (!file_put_contents($filePath, "<?php\nreturn " . var_export($compiledRoutes, true) . ";\n")) {
                throw new \RuntimeException("Failed to save features to temporary file");
            }

            if (!rename($filePath, self::CACHE_FILE)) {
                throw new \RuntimeException("Failed to rename temporary file");
            }
        } catch (\RuntimeException $e) {
            if (file_exists($filePath)) {
                unlink($filePath);
                $fileDeleted = true;
            }

            return false;

        } finally {
            if (file_exists($filePath) && !$fileDeleted) {
                unlink($filePath);
                return true;
            }
        }

        return false;
    }

    /**
     * Loads cached routes
     *
     * @param RequestContext $context
     */
    private function getCachedRoutes(): array
    {
        /** @var array $compiledRoutes */
        $compiledRoutes = require_once self::CACHE_FILE;
        return $compiledRoutes;
    }

    public function getRoutes(): RouteCollection
    {
        return $this->routes;
    }
}

