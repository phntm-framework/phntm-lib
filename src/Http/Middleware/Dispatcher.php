<?php

namespace Phntm\Lib\Http\Middleware;

use Phntm\Lib\Pages\EndpointInterface;
use Psr\Http\Message\ResponseFactoryInterface;

class Dispatcher implements \Psr\Http\Server\MiddlewareInterface
{
    public function __construct(
        private ResponseFactoryInterface $responseFactory
    ) { }

    /**
     * Renders the page provided by the router, or return a response with an
     * appropriate status code if not.
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(
        \Psr\Http\Message\ServerRequestInterface $request, 
        \Psr\Http\Server\RequestHandlerInterface $handler
    ): \Psr\Http\Message\ResponseInterface {
        $page = $request->getAttribute('page', 404);

        if (!$page instanceof EndpointInterface) {
            return $this->responseFactory->createResponse($page);
        }

        // dont process the middleware stack as this is last in the chain
        $response = $this->responseFactory->createResponse();

        // render the page content
        $body = $page->dispatch($request);

        if ($body->getSize() === 0) {
            return $response->withStatus(204);
        }

        $response = $response->withHeader('Content-Type', $page->getContentType());

        // return response with page body
        return $response->withBody($body);
    }
}
