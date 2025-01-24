<?php

namespace Phntm\Lib\Pages;

use Symfony\Component\HttpFoundation\Request;
use Psr\Http\Message\StreamInterface;

interface PageInterface
{
    public function render(Request $request): StreamInterface;
}
