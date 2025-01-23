<?php

namespace Phntm\Lib\View\Twig;

use Twig\Extension\AbstractExtension;
use Twig\Extension\ExtensionInterface;

class Extension extends AbstractExtension implements ExtensionInterface
{
    public function getFunctions(): array
    {
        return [
            new \Twig\TwigFunction('dd', [$this, 'dd']),
        ];
    }

}
