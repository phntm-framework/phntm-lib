<?php

namespace Phntm\Lib\Model\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class fuck extends Base
{
    public string $columnType = 'string';

    public function __construct(
        public ?string $label = null,
        public string $placeholder = '',
        public bool $required = false,
        public int $size = 255,
        public ?int $minlength = null,
        public ?int $maxlength = null,
    ) {
    }

    public function getOptions(): array
    {
        return [
            'length' => $this->size,
        ];
    }
}
