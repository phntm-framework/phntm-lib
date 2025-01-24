<?php

namespace Pages\AuthLogin\Provider;

use Phntm\Lib\Pages\Endpoint;
use Phntm\Lib\Http\Redirect;
use Symfony\Component\HttpFoundation\Request;
use Phntm\Lib\Infra\Routing\Attributes\Dynamic;

#[Dynamic('Pages\AuthLogin\{string:provider}')]
class Page extends Endpoint
{
    public function __invoke(Request $request): void
    {
        throw new Redirect($request->headers->get('referer'), 302);
    }
}
