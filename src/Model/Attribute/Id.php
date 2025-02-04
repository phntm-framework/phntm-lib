<?php

namespace Phntm\Lib\Model\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Id extends Integer
{
    public bool $hidden = false;

    public function getOptions(): array
    {
        return [
            'autoincrement' => true,
        ];
    }
}
