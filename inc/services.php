<?php

/** @var League\Container\DefinitionContainerInterface $container */

//$container->addShared(Psr\Http\Server\RequestHandlerInterface::class, Relay\Relay::class);

$container->addShared(Phntm\Lib\Site\Navigation\ResolverInterface::class, Phntm\Lib\Site\Navigation\SimplePageResolver::class);
