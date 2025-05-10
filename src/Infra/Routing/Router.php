<?php

namespace Phntm\Lib\Infra\Routing;

use League\Container\ContainerAwareInterface;
use League\Container\ContainerAwareTrait;
use Phntm\Lib\Infra\Debug\Aware\DebugAwareInterface;
use Phntm\Lib\Infra\Debug\Aware\DebugAwareTrait;
use Phntm\Lib\Pages\Endpoint;
use Phntm\Lib\Pages\EndpointInterface;
use Phntm\Lib\Routing\RouterInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Routing\Matcher\Dumper\CompiledUrlMatcherDumper;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\Matcher\CompiledUrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;

/**
 * Handles routing and pages
 *
 * gathers autoloaded classes from composer and checks route matches against 
 * existing namespaces
 */
class Router implements RouterInterface, ContainerAwareInterface, DebugAwareInterface
{
    use ContainerAwareTrait;
    use DebugAwareTrait;

    private const CACHE_FILE = ROOT . '/tmp/cache/routes.php';
    private const STATIC_SEGMENT_WEIGHT = 256;  // 2^8
    private const DYNAMIC_SEGMENT_WEIGHT = 16;  // 2^4
    private const POSITION_MULTIPLIER = 8;  // 2^3

    public RouteCollection $routes;

    private UrlMatcher $matcher;

    public ?string $notFound = null;

    public array $registeredManagePages = [];
    public array $unregisteredManagePages = [];

    protected RequestContext $requestContext;

    public function setRequest(ServerRequestInterface $request): static
    {
        $context = new RequestContext();

        $context->setPathInfo(rtrim($context->getPathInfo(), '/'));

        $this->requestContext = $context;

        if (file_exists(self::CACHE_FILE) && !$this->debug()->enabled()) {
            $this->debug()->log('Using cached routes', 'info');

            $compiledRoutes = $this->getCachedRoutes();

            $this->matcher = new CompiledUrlMatcher($compiledRoutes, $context);

        } else {
            $this->indexRoutes();

            $this->matcher = new UrlMatcher($this->routes, $context);

            if (!$this->debug()->enabled()) {
                $this->cacheRoutes();
            }
        }

        return $this;
    }

    /**
     * Gathers all Pages\\ routes from autoloaded classes and adds them to the 
     * RouteCollection
     *
     * parses Dynamic attributes to gather route variables and their types
     */
    public function indexRoutes(): void
    {
        $this->debug()->startMeasure('router.index', 'Index Routes');
        $this->routes = new RouteCollection();

        $classes = $this->autoload();

        foreach ($classes as $pageClass => $path) {
            if (is_a($pageClass, Endpoint::class, true)) {
                $pageClass::registerRoutes($this->routes);
            }
        }
        $this->debug()->stopMeasure('router.index');
    }

    /**
     * Dispatches a route from a given request and returns a page or status code
     *
     * @returns EndpointInterface | int
     */
    public function dispatch(): EndpointInterface | int
    {
        $this->debug()->startMeasure('router.dispatch', 'Dispatch Route');
        try {
            $attributes = $this->matcher->match($this->requestContext->getPathInfo());
            $this->debug()->log($attributes, 'info');

            $parts = explode('::', $attributes['_route']);
            $attributes['_route'] = $parts[0];

            if (!class_exists($attributes['_route'])) {
                throw new \Symfony\Component\Routing\Exception\ResourceNotFoundException('Page not found');
            }

            $route = $attributes['_route'];
            unset($attributes['_route']);

            /** @var EndpointInterface $page */
            $page = $this->getContainer()->get($route);
            $page->setDynamicParams($attributes);


            if (isset($parts[1])) {
                $page->matchedAction = $parts[1];
            }

            return $page;

        } catch (\Symfony\Component\Routing\Exception\ResourceNotFoundException $exception) {

            // if matcher fails
            return $this->notFound ? new $this->notFound : 404;
        } catch (\Exception $exception) {

            dd($exception);
            // if any error occurs
            return 500;
        } finally {
            $this->debug()->stopMeasure('router.dispatch');
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
        $namespace = preg_replace('/\\\Manage$/', '', $namespace);
        $namespace = ltrim($namespace, 'Pages');

        $namespace = explode('\\', $namespace);
        foreach ($namespace as $key => $part) {
            $namespace[$key] = strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $part));
        }
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
        $compiledRoutes = require self::CACHE_FILE;

        return $compiledRoutes;
    }

    public function getRoutes(): RouteCollection
    {
        return $this->routes;
    }

    public static function calcRoutePriority(string $path): int
    {
        // Remove leading/trailing slashes and split into segments
        $segments = array_filter(explode('/', trim($path, '/')));
        
        if (empty($segments)) {
            return 9999;
        }

        $priority = 0;
        $position = count($segments);

        foreach ($segments as $segment) {
            // Check if segment is dynamic (contains {} characters)
            $isDynamic = preg_match('/\{.*\}/', $segment);
            
            // Base segment score based on type
            $segmentScore = $isDynamic 
                ? self::DYNAMIC_SEGMENT_WEIGHT 
                : self::STATIC_SEGMENT_WEIGHT;
            
            // Add position-weighted score
            $priority += $segmentScore + ($position * self::POSITION_MULTIPLIER);
            
            $position--;
        }

        return $priority;
    }
}
