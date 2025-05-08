<?php

namespace Phntm\Lib\Http\Middleware;

use Phntm\Lib\Http\Redirect as ThrownRedirect;
use Phntm\Lib\Di\Container;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseFactoryInterface;

class Redirect implements \Psr\Http\Server\MiddlewareInterface
{
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        try {
            $response = $handler->handle($request);
        } catch (ThrownRedirect $redirect) {
            return Container::get()->get(ResponseFactoryInterface::class)
                ->createResponse()
                ->withHeader('Location', $redirect->getMessage())
                ->withStatus($redirect->getCode());
        }

        return $response;
    }
}
