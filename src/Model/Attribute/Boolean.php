<?php

namespace Phntm\Lib\Model\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Boolean extends Base
{
    public string $columnType = 'boolean';

    public string $inputTemplate = 'checkbox';

    public function __construct(
        public ?string $label = null,
        public string $placeholder = '',
        public bool $required = false,
        public int $size = 255,
        public bool $unsigned = false,
        public bool $hidden = false,
    ) {
    }

    public function getOptions(): array
    {
        return [
            'length' => $this->size,
        ];
    }

    public function isHidden(): bool
    {
        return $this->hidden;
    }
}
