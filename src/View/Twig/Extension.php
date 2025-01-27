<?php

namespace Phntm\Lib\View\Twig;

use Twig\Extension\AbstractExtension;
use Twig\Extension\ExtensionInterface;

class Extension extends AbstractExtension implements ExtensionInterface
{
    public function getFunctions(): array
    {
        return [
            new \Twig\TwigFunction('attr', [$this, 'attr'], ['is_safe' => ['html']]),
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

}
