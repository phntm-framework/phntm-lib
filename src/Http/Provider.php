<?php

namespace Phntm\Lib\Http;

use Middlewares\Whoops;
use Nyholm\Psr7Server\ServerRequestCreator;
use Nyholm\Psr7Server\ServerRequestCreatorInterface;
use Nyholm\Psr7\Factory\Psr17Factory;
use Phntm\Lib\Di\ModuleProvider;
use DebugBar\DebugBar;
use Middlewares\Debugbar as DebugMiddleware;
use Phntm\Lib\Pages\EndpointInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;

class Provider extends ModuleProvider
{
    public function provides(string $id): bool
    {
        $services = [
            Whoops::class,
            DebugMiddleware::class,
            EndpointInterface::class,
            ResponseFactoryInterface::class,
            UploadedFileFactoryInterface::class,
            UriFactoryInterface::class,
            StreamFactoryInterface::class,
            ServerRequestCreatorInterface::class,
            ServerRequestInterface::class,
        ];
        
        return in_array($id, $services);
    }

    public function definitions(): void
    {
        $this->getContainer()->addShared(EndpointInterface::class);

        $this->getContainer()->add(Whoops::class, Whoops::class)
            ->addArgument(null)
            ->addArgument(ResponseFactoryInterface::class)
        ;

        $this->getContainer()->add(DebugMiddleware::class)
            ->addArgument(DebugBar::class)
            ->addMethodCall('inline')
        ;

        $this->getContainer()->addShared(ResponseFactoryInterface::class, Psr17Factory::class);
        $this->getContainer()->addShared(UploadedFileFactoryInterface::class, Psr17Factory::class);
        $this->getContainer()->addShared(UriFactoryInterface::class, Psr17Factory::class);
        $this->getContainer()->addShared(StreamFactoryInterface::class, Psr17Factory::class);

        $this->getContainer()->addShared(ServerRequestCreatorInterface::class, ServerRequestCreator::class)
            ->addArgument(ResponseFactoryInterface::class)
            ->addArgument(UriFactoryInterface::class)
            ->addArgument(UploadedFileFactoryInterface::class)
            ->addArgument(StreamFactoryInterface::class)
        ;

        $container = $this->getContainer();

        $this->getContainer()->add(ServerRequestInterface::class, function (ServerRequestCreatorInterface $serverRequestFactory) {
            return $serverRequestFactory->fromGlobals();
        })->addArgument(ServerRequestCreatorInterface::class);

    }
}

