<?php

namespace Phntm\Lib\Infra;

use Phntm\Lib\Cache\Provider as CacheModule;
use Phntm\Lib\Config\Provider as ConfigModule;
use Phntm\Lib\Db\Provider as DbModule;
use Phntm\Lib\Di\ContainerAware;
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
use Symfony\Component\Yaml\Yaml;
use function microtime;
use function ob_clean;

class Server implements DebugAwareInterface, RequestHandlerInterface
{
    use ContainerAware;
    use DebugAwareTrait;

    private RequestHandlerInterface $requestHandler;

    private array $modules = [];

    private array $middleware = [];

    private array $bootstrap = [];

    public static string $config;

    private $start_time;

    public function __construct() {
        ob_start();
        $this->start_time = microtime(true);

        define('PHNTM', '/vendor/phntm-framework/phntm-lib/');
        $this->bootstrap = Yaml::parseFile(ROOT . '/bootstrap.yml');

        $this->provision(
            $this->bootstrap['modules'] ?? [],
        );

        $this->middleware = $this->bootstrap['middleware'] ?? [];
    }

    public function provision(array $modules = []): void {
        if ($this->bootstrap['include_phntm_modules'] ?? true) {
            $modules = array_merge(
                $this->getDefaultModules(),
                $modules,
            );
        }

        foreach ($modules as $module) {
            $this->registerModule($module);
        }

        $debugbar = $this->getContainer()->get(DebugBar::class);
        $this->setDebugBar($debugbar);
    }

    public function run(): void
    {
        $this->debug()->getCollector('time')->addMeasure(
            'Server init',
            $this->start_time,
            microtime(true),
        );

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

        if (
            !$this->debug()->enabled()
            && is_a($entry, DebugMiddleware::class, true)
        ) {
            $entry = current($this->middleware);
            next($this->middleware);
        }

        if (is_string($entry)) {
            $entryInstance = $this->getContainer()->get($entry);
        }

        return $entryInstance->process($request, $this);
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
