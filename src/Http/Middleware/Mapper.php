<?php

namespace Phntm\Lib\Http\Middleware;

use Phntm\Lib\Infra\Routing\Router as PhntmRouter;

class Mapper implements \Psr\Http\Server\MiddlewareInterface
{
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
        $request = $request->withAttribute('mapped_request', new \StdClass());

        $response = $handler->handle($request);

        return $response;
    }
}
