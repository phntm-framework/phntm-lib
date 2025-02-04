<?php

namespace Phntm\Lib\Infra\Routing\Attributes;

use Attribute;

/**
 * Overrides the default route for a page
 */
#[Attribute]
class Alias
{
    public function __construct(public string $alias)
    {
    }
}
