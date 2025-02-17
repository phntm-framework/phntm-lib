<?php

namespace Pages\ForgotPassword;

use Phntm\Lib\Infra\Routing\Router;
use Phntm\Lib\Pages\RichPage;
use Symfony\Component\HttpFoundation\Request;

class Manage extends RichPage
{
    public string $body_class = 'min-h-screen bg-gray-100 py-12 px-2 sm:px-6 lg:px-8 flex justify-center items-center';

    public function __invoke(Request $request): void
    {
        $this->withScript('https://unpkg.com/@tailwindcss/browser@4');
    }

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
