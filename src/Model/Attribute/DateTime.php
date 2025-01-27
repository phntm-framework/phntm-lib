<?php

namespace Phntm\Lib\Model\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class DateTime extends Base
{
    public string $columnType = 'datetime';

    public function __construct(
        public ?string $label = null,
        public bool $required = false,
    ) {
    }

    public function getDbValue(): mixed
    {
        /** @var /DateTime $dt */
        $dt = parent::getDbValue();
        return $dt->format('Y-m-d H:i:s');
    }

    public function fromDbValue(mixed $value): mixed
    {
        return new \DateTime($value);
    }

    public function getOptions(): array
    {
        return [
            'length' => 0,
        ];
    }
}
