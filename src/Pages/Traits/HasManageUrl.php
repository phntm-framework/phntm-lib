<?php

namespace Phntm\Lib\Pages\Traits;

use Phntm\Lib\Infra\Routing\Router;

/**
 * This trait is used to add the manage prefix to the route path without 
 * extending \Phntm\Lib\Pages\AbstractManagePage
 */
trait HasManageUrl
{
    protected static function resolveBaseRoute(): array
    {
        $route = parent::resolveBaseRoute();
        $pathParts = explode('/', ltrim($route['path'], '/'));
        if (end($pathParts) === 'manage') {
            array_pop($pathParts);
        }
        // add manage to the start of the path
        array_unshift($pathParts, 'manage');
        $route['path'] = '/' . implode('/', $pathParts);
        $route['priority'] = Router::calcRoutePriority($route['path']);
        return $route;
    }
}
