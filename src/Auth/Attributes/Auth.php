<?php

namespace Phntm\Lib\Auth\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Auth
{
    public function __construct(
        public array|string $roles = '*',
    ) {
    }
}
