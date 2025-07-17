<?php

namespace Phntm\Lib\Http\Middleware;

use Phntm\Lib\Routing\RouterInterface;

class Router implements \Psr\Http\Server\MiddlewareInterface
{
    public function __construct(
        protected RouterInterface $router,
    ) { }

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
    ): \Psr\Http\Message\ResponseInterface
    {
        $page = $this->router
            ->setRequest($request)
            ->dispatch()
        ;

        // pass the page up the middleware stack
        $response = $handler->handle($request->withAttribute('page', $page));

        return $response;
    }
}
