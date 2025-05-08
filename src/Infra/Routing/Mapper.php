<?php

namespace Phntm\Lib\Infra\Routing;

class Mapper
{
    private $routes = [];

    public function addRoute(Route $route)
    {
        $this->routes[] = $route;
    }

    public function getRoutes()
    {
        return $this->routes;
    }
}
