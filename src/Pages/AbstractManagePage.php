<?php

namespace Phntm\Lib\Pages;

use Psr\Http\Message\StreamInterface;

abstract class AbstractManagePage extends AbstractPage implements Manageable
{
    final public function render($request): StreamInterface
    {
        $this->title('Manage Content');

        return parent::render($request);
    }
}
