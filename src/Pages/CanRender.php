<?php

namespace Phntm\Lib\Pages;

use Psr\Http\Message\StreamInterface;

interface CanRender
{
    public function preRender(): void;
    public function render(): StreamInterface;

    public function getViewVariables(): array;
}
