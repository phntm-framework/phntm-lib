<?php

namespace Phntm\Lib\Infra;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Phntm\Lib\Http\Middleware\Router;
use Phntm\Lib\Http\Middleware\Dispatcher;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;
use Middlewares\Whoops;
use Middlewares\Debugbar;
use Relay\Relay;

class Server
{
    private RequestHandlerInterface $requestHandler;

    private ServerRequestInterface $request;

    public function __construct(?string $services=null)
    {
        Debug\Debugger::init();

        Debug\Debugger::getBar()['time']->startMeasure('server-init', 'Server Initialization');

        $responseFactory = new Psr17Factory();
        $serverRequestFactory = new ServerRequestCreator(
            $responseFactory, // ServerRequestFactory
            $responseFactory, // UriFactory
            $responseFactory, // UploadedFileFactory
            $responseFactory  // StreamFactory
        );

        $this->request = $serverRequestFactory->fromGlobals();

        // free up 
        $serverRequestFactory = null;

        $middleware = [
            new Whoops(),
            'debug' => (new Debugbar(
                Debug\Debugger::getBar()
            ))->inline(),
            new Router($responseFactory),
            new Dispatcher($responseFactory), // must go last
        ];

        if (!Debug\Debugger::$enabled) {
            unset($middleware['debug']);
        }

        $this->requestHandler = new Relay($middleware);

        Debug\Debugger::getBar()['time']->stopMeasure('server-init');
    }

    public function run(): void
    {
        $response = $this->requestHandler->handle($this->request);

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
}
