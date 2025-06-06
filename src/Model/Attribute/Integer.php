<?php

namespace Phntm\Lib\Model\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Integer extends Base
{
    public string $columnType = 'integer';

    public string $inputTemplate = 'input';

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
