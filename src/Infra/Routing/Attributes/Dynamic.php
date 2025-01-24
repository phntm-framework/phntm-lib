<?php

namespace Phntm\Lib\Infra\Routing\Attributes;

use Attribute;

#[Attribute]
class Dynamic
{
    public function __construct(private string $denoted_namespace)
    {
    }
}
