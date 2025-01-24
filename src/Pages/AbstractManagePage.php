<?php

namespace Phntm\Lib\Pages;

use Psr\Http\Message\StreamInterface;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractManagePage extends AbstractPage implements Manageable
{
    final public function render(Request $request): StreamInterface
    {
        parent::render($request);
        $this->withInlineCss('');
        $this->withBodyClass('min-h-screen bg-gray-100 py-12 px-2 sm:px-6 lg:px-8');
    }
}
