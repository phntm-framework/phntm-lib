<?php

namespace Phntm\Lib\Model\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class TextArea extends Base
{
    public string $columnType = 'text';

    public function __construct(
        public ?string $label = null,
        public int $rows = 3,
        public bool $required = false,
    ) {
    }

    public function getOptions(): array
    {
        return [ ];
    }
}
