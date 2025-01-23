<?php

namespace Phntm\Lib\Infra;

use Phntm\Lib\Http\Middleware\Auth;
use Phntm\Lib\Http\Middleware\Dispatcher;
use Phntm\Lib\Http\Middleware\Redirect;
use Phntm\Lib\Http\Middleware\Router;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Middlewares\Whoops;
use Middlewares\Debugbar;
use Relay\Relay;
use function realpath;

class Server
{
    private RequestHandlerInterface $requestHandler;

    public function __construct(?string $services=null)
    {
        // Phntm services
        $this->loadServices(realpath(__DIR__ . '/../../services.php'));

        if (null !== $services) {
            // Site level services
            $this->loadServices(ROOT . '/' . ltrim($services, '/'));
        }

        Debug\Debugger::init();

        Debug\Debugger::getBar()['time']->startMeasure('server-init', 'Server Initialization');

        $middleware = [
            new Whoops(
                responseFactory: Container::get()->get(ResponseFactoryInterface::class)
            ),
            new Redirect(),
            'debug' => (new Debugbar(
                Debug\Debugger::getBar()
            ))->inline(),
            new Redirect(),
            new Router(),
            new Auth(),
            new Dispatcher(),
        ];

        if (!Debug\Debugger::$enabled) {
            unset($middleware['debug']);
        }

        $this->requestHandler = new Relay($middleware);

        Debug\Debugger::getBar()['time']->stopMeasure('server-init');

    }

    public function run(): void
    {
        $response = $this->requestHandler->handle(
            Container::get()->get(ServerRequestInterface::class)
        );

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

    public function loadServices(string $services): void
    {
        if (file_exists($services)) {
            $container = Container::get();
            require_once $services;
        } else {
            throw new \Exception('Services file not found at ' . $services);
        }
    }
}
