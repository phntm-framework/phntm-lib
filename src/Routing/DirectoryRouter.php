<?php

namespace Phntm\Lib\Routing;

use League\Container\ContainerAwareInterface;
use League\Container\ContainerAwareTrait;
use Phntm\Lib\Infra\Debug\Aware\DebugAwareInterface;
use Phntm\Lib\Infra\Debug\Aware\DebugAwareTrait;
use Phntm\Lib\Pages\Endpoint;
use Phntm\Lib\Pages\EndpointInterface;
use Phntm\Lib\Pages\NotFoundPage;
use Phntm\Lib\Routing\Cache\RouteCacheInterface;
use Phntm\Lib\Routing\RouterInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\Matcher\CompiledUrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use function boolval;
use function get_declared_classes;

/**
 * Handles routing and pages
 *
 * gathers autoloaded classes from composer and checks route matches against 
 * existing namespaces
 */
class DirectoryRouter implements RouterInterface, ContainerAwareInterface, DebugAwareInterface
{
    use ContainerAwareTrait;
    use DebugAwareTrait;

    public RouteCollection $routes;

    private UrlMatcher $matcher;

    public string|int $notFound = 404;

    protected RequestContext $requestContext;

    public function __construct(
        protected RouteCacheInterface $routeCache,
        protected RedisAdapter $cache,
    ){
    }

    public function setRequest(ServerRequestInterface $request): static
    {
        $this->debug()->startMeasure('phntm.routing.router.total', 'Routing');
        $this->requestContext = new RequestContext(
            '',
            $request->getMethod(),
            $request->getUri()->getHost(),
            $request->getUri()->getScheme(),
            path: rtrim($request->getUri()->getPath(), '/'),
            queryString: $request->getUri()->getQuery(),
        );

        $routes = $this->routeCache->get();
        $routes = null;
        if (null !== $routes) {
            $this->matcher = new CompiledUrlMatcher($routes, $this->requestContext);
        } else {
            $this->indexRoutes();

            $this->matcher = new UrlMatcher($this->routes, $this->requestContext);

            $this->routeCache->set($this->routes);
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
        $this->debug()->startMeasure('phntm.routing.router.index', 'Index Routes');
        $this->routes = new RouteCollection();

        $classes = $this->autoload();

        foreach ($classes as $pageClass => $path) {
            if (is_a($pageClass, NotFoundPage::class, true)) {
                $this->notFound = $pageClass;
                continue;
            }
            if (is_a($pageClass, Endpoint::class, true)) {
                $pageClass::registerRoutes($this->routes);
            }
        }
        $this->debug()->stopMeasure('phntm.routing.router.index');
    }

    /**
     * Dispatches a route from a given request and returns a page or status code
     */
    public function dispatch(): EndpointInterface|int
    {
        $this->debug()->startMeasure('phntm.routing.router.dispatch', 'Dispatch Route');
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
            return $this->getContainer()->get($this->notFound) ?? 404;
        } catch (\Exception $exception) {

            dd($exception);
            // if any error occurs
            return 500;
        } finally {
            $this->debug()->stopMeasure('phntm.routing.router.dispatch');
            $this->debug()->stopMeasure('phntm.routing.router.total');
        }
    }

    /**
     * Autoloads the classes from composer
     *
     * @returns array<string>
     */
    protected function autoload(): array
    {
        $shouldCache = 'false' !== getenv('CACHE_ROUTES');
        if ($shouldCache) {
            $cache = $this->cache->getItem('phntm.routing.router.autoload');
            if ($cache->isHit()) {
                return unserialize($cache->get());
            }
        }

        $autoloaderClassName = '';
        foreach (get_declared_classes() as $className) {
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

        if ($shouldCache) {
            $cache->set(serialize($classes));
            $cache->expiresAfter(3600);
            $this->cache->save($cache);
        }

        return $classes;
    }

    public function getRoutes(): RouteCollection
    {
        return $this->routes;
    }
}
