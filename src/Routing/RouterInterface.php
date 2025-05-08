<?php

namespace Phntm\Lib\Routing;

use Psr\Http\Message\ServerRequestInterface;

interface RouterInterface
{
    public function setRequest(ServerRequestInterface $request): static;

    public function dispatch(): mixed;
}
