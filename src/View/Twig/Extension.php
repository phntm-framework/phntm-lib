<?php

namespace Phntm\Lib\View\Twig;

use Phntm\Lib\Config;
use Phntm\Lib\Di\Container;
use Twig\Extension\AbstractExtension;
use Twig\Extension\ExtensionInterface;
use Twig\Extension\GlobalsInterface;

class Extension extends AbstractExtension implements ExtensionInterface, GlobalsInterface
{
    public function getFunctions(): array
    {
        return [
            new \Twig\TwigFunction('attr', [$this, 'attr'], ['is_safe' => ['html']]),
            new \Twig\TwigFunction('config', [Config::class, 'retrieve']),
        ];
    }

    public function attr(array $attributes): string
    {
        $result = '';
        foreach ($attributes as $key => $value) {
            if (!$value) {
                continue;
            }
            $result .= $key . '="' . $value . '" ';
        }
        return $result;
    }

    public function getGlobals(): array
    {
        return [
            'Container' => Container::get(),
        ];
    }

}
