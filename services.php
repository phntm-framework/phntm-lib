<?php

/** @var League\Container\DefinitionContainerInterface $container */

$container->add(Psr\Http\Message\ResponseFactoryInterface::class, Nyholm\Psr7\Factory\Psr17Factory::class);
$container->add(Psr\Http\Message\UriFactoryInterface::class, Nyholm\Psr7\Factory\Psr17Factory::class);
$container->add(Psr\Http\Message\UploadedFileFactoryInterface::class, Nyholm\Psr7\Factory\Psr17Factory::class);
$container->add(Psr\Http\Message\StreamFactoryInterface::class, Nyholm\Psr7\Factory\Psr17Factory::class);

$container->add(Psr\Http\Message\ServerRequestInterface::class, function () use ($container) {
    $serverRequestFactory = new Nyholm\Psr7Server\ServerRequestCreator(
        $container->get(Psr\Http\Message\ResponseFactoryInterface::class), // ServerRequestFactory
        $container->get(Psr\Http\Message\UriFactoryInterface::class), // UriFactory
        $container->get(Psr\Http\Message\UploadedFileFactoryInterface::class), // UploadedFileFactory
        $container->get(Psr\Http\Message\StreamFactoryInterface::class), // StreamFactory
    );

    return $serverRequestFactory->fromGlobals();
});
