<?php

namespace Phntm\Lib\Infra;

use Phntm\Lib\Cache\Provider as CacheModule;
use Phntm\Lib\Config\Provider as ConfigModule;
use Phntm\Lib\Db\Provider as DbModule;
use Phntm\Lib\Di\ContainerAware;
use Phntm\Lib\Http\Middleware\Auth;
use Phntm\Lib\Http\Middleware\Dispatcher;
use Phntm\Lib\Http\Middleware\Redirect;
use Phntm\Lib\Http\Middleware\Router;
use Phntm\Lib\Http\Provider as HttpModule;
use Phntm\Lib\Images\Provider as ImageModule;
use Phntm\Lib\Infra\Debug\Aware\DebugAwareInterface;
use Phntm\Lib\Infra\Debug\Aware\DebugAwareTrait;
use DebugBar\DebugBar;
use Phntm\Lib\Routing\Provider as RoutingModule;
use Phntm\Lib\View\Provider as ViewModule;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Middlewares\Whoops;
use Middlewares\Debugbar as DebugMiddleware;
use function class_exists;
use function microtime;
use function ob_clean;
use function realpath;
use function serialize;

class Server implements DebugAwareInterface, RequestHandlerInterface
{
    use ContainerAware;
    use DebugAwareTrait;

    private RequestHandlerInterface $requestHandler;

    private array $modules = [];

    private array $middleware = [];

    public static string $config;

    /**
     * @param array<string> $modules
     * @param array<class-string<MiddlewareInterface>> $middleware
     * @param string|null $config
     */
    public function __construct(
        array $modules = [],
        array $middleware = [
            Whoops::class,
            DebugMiddleware::class,
            Redirect::class,
            Router::class,
            Auth::class,
            Dispatcher::class,
        ],
        ?string $config = null
    ) {
        static::$config = $config;
        $this->provision($modules, $config);

        $this->debug()->startMeasure('server-init', 'Server Initialization');

        if (!$this->debug()->enabled()) {
            $middleware = array_filter($middleware, function ($item) {
                $nonProd = [
                    Whoops::class,
                    DebugMiddleware::class,
                ];
                return !in_array($item, $nonProd);
            });
        }

        $this->middleware = array_map(
            function ($middleware) {
                $this->debug()->log('Middleware: ' . $middleware, 'middleware');
                return $this->getContainer()->get($middleware);
            },
            $middleware
        );

        $this->debug()->stopMeasure('server-init');
    }

    public function provision(
        array $modules = [],
        ?string $config = null
    ): void {
        // Dependency Injection Container

        $register_start_time = microtime(true);
        foreach ($modules as $module) {
            $this->registerModule($module);
        }

        $register_end_time = microtime(true);


        $debugbar = $this->getContainer()->get(DebugBar::class);
        $this->setDebugBar($debugbar);

        $this->debug()->getCollector('time')->addMeasure(
            'Registering Modules',
            $register_start_time,
            $register_end_time,
        );
    }

    public function run(): void
    {
        $response = $this->handle(
            $this->getContainer()->get(ServerRequestInterface::class)
        );

        ob_clean();

        $http_line = sprintf('HTTP/%s %s %s',
            $response->getProtocolVersion(),
            $response->getStatusCode(),
            $response->getReasonPhrase()
        );

        header($http_line, true, $response->getStatusCode());

        foreach ($response->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                header("$name: $value", false);
            }
        }

        $stream = $response->getBody();

        if ($stream->isSeekable()) {
            $stream->rewind();
        }

        while (!$stream->eof()) {
            echo $stream->read(1024 * 8);
        }
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $entry = current($this->middleware);
        next($this->middleware);
        return $entry->process($request, $this);
    }

    public function loadServices(string $services): void
    {
        if (file_exists($services)) {
            $container = $this->getContainer();
            require_once $services;
        } else {
            throw new \Error('Services file not found at ' . $services);
        }
    }

    public function registerModule(string $module): void
    {
        $container = $this->getContainer();

        $module = new $module();
        $container->addServiceProvider($module);

        $this->modules[] = $module;
    }

    public function loadModules(string $modules): void
    {
        if (file_exists($modules)) {
            $server = $this;
            require_once $modules;
            unset($server);
        } else {
            throw new \Error('Module definition file not found at ' . $modules);
        }
    }

    public static function getDefaultModules(): array
    {
        return [
            ConfigModule::class,
            Provider::class,
            DbModule::class,
            CacheModule::class,
            HttpModule::class,
            ImageModule::class,
            ViewModule::class,
            RoutingModule::class,
        ];
    }
}
