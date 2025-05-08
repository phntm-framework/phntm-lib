<?php

namespace Pages\AuthLogin\Provider;

use Phntm\Lib\Pages\Endpoint;
use Phntm\Lib\Http\Redirect;
use Phntm\Lib\Infra\Routing\Attributes\Dynamic;

#[Dynamic('Pages\AuthLogin\{string:provider}')]
class Page extends Endpoint
{
    public static bool $hideFromSitemap = true;

    public function __invoke(): never
    {
        throw new Redirect(current($this->getRequest()->getHeader('referer')));
    }
}
