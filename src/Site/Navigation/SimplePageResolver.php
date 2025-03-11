<?php

namespace Phntm\Lib\Site\Navigation;

use Phntm\Lib\Model\SimplePage;

class SimplePageResolver implements ResolverInterface
{
    public function resolve(): array
    {
        return SimplePage::where('include_in_nav', true);
    }
}
