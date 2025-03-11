<?php

namespace Phntm\Lib\Http\Middleware;

use Phntm\Lib\Di\Container;
use Phntm\Lib\Pages\EndpointInterface;
use Phntm\Lib\Pages\PageInterface;
use Psr\Http\Message\ResponseFactoryInterface;

class Dispatcher implements \Psr\Http\Server\MiddlewareInterface
{
    private ResponseFactoryInterface $responseFactory;
    /**
     * Dispatcher constructor.
     * setup the response factory
     *
     * @param ResponseFactoryInterface $responseFactory
     */
    public function __construct() {
        $this->responseFactory = Container::get()->get(ResponseFactoryInterface::class);
    }

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
        $body = $page->dispatch($request->getAttribute('symfonyRequest'));

        if ($body->getSize() === 0) {
            if (!isLocal()) {
                return $response->withStatus(204);
            }
            $body->write('Page body is empty - likely no view.twig or view.twig is empty');
        }

        $response = $response->withHeader('Content-Type', $page->getContentType());

        // return response with page body
        return $response->withBody($body);
    }
}
