<?php

namespace Phntm\Lib\Routing\Classic;

use Phntm\Lib\Pages\EndpointInterface;
use Phntm\Lib\Routing\RouterInterface;
use Psr\Http\Message\ServerRequestInterface;

class ClassicRouter implements RouterInterface
{
    protected array $routes = [];
    protected array $params = [];
    protected string $requestMethod;
    protected string $requestUri;

    public function setRequest(ServerRequestInterface $request): static
    {
        $this->requestMethod = $request->getMethod();
        $this->requestUri = $request->getUri()->getPath();

        return $this;
    }

    public function dispatch(): EndpointInterface
    {
    }
}
