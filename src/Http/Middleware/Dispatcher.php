<?php

namespace Phntm\Lib\Http\Middleware;

use League\Container\ContainerAwareInterface;
use League\Container\ContainerAwareTrait;
use Phntm\Lib\Pages\EndpointInterface;
use Phntm\Lib\Routing\RouterInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class Dispatcher implements \Psr\Http\Server\MiddlewareInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function __construct(
        protected ResponseFactoryInterface $responseFactory,
        protected RouterInterface $router,
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

        if (!$page instanceof EndpointInterface && $page !== 404) {
            return $this->responseFactory->createResponse($page);
        } else if ($page === 404) {
            $page = $this->getContainer()->get($this->router->notFound);
        }



        // dont process the middleware stack as this is last in the chain
        $response = $this->responseFactory->createResponse();

        try {

            // render the page content
            $body = $page->dispatch($request);


        } catch (ResourceNotFoundException $e) {
            $body = $this
                ->getContainer()
                ->get($this->router->notFound)
                ->dispatch($request)
            ;
        }

        if ($body->getSize() === 0) {
            return $response->withStatus(204);
        }

        $response = $response->withHeader('Content-Type', $page->getContentType());

        // return response with page body
        return $response->withBody($body);
    }
}
