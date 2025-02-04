<?php

namespace Phntm\Lib\Pages\Traits;

use Phntm\Lib\Infra\Routing\Router;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

trait CanGenerateUrls
{
    private static UrlGeneratorInterface $urlGenerator;

    public static function getUrlGenerator(): UrlGeneratorInterface
    {
        /*$router = new Router();
        $generator = new UrlGenerator($router->getRoutes(), $router->getContext());
        return self::$urlGenerator;*/
    }
}
