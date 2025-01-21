<?php

namespace Phntm\Lib\Http\Middleware;

use Phntm\Lib\Infra\Routing\Router as PhntmRouter;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Psr\Http\Message\ResponseFactoryInterface;

class Router implements \Psr\Http\Server\MiddlewareInterface
{
    /**
     * Router constructor.
     * setup the response factory
     *
     * @param ResponseFactoryInterface $responseFactory
     */
    public function __construct(private ResponseFactoryInterface $responseFactory) {}

    /**
     * Route a request to a defined Page, or return a relevant status code.
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(
        \Psr\Http\Message\ServerRequestInterface $request, 
        \Psr\Http\Server\RequestHandlerInterface $handler
    ): \Psr\Http\Message\ResponseInterface {
        // convert PSR-7 request to Symfony request for use in router
        $httpFoundationFactory = new HttpFoundationFactory();
        $symfonyRequest = $httpFoundationFactory->createRequest($request);

        $request = $request->withAttribute('symfonyRequest', $symfonyRequest);
        
        // the router will return a found page or a relevant status code
        $page = (new PhntmRouter($request->getAttribute('symfonyRequest')))->dispatch();

        // pass the page up the middleware stack
        $response = $handler->handle($request->withAttribute('page', $page));

        return $response;
    }
}
