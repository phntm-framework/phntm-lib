<?php

namespace Phntm\Lib\Model\Attribute;

use Attribute;
use function is_string;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Date extends Base
{
    public string $columnType = 'date';

    public string $inputTemplate = 'date';

    public static string $dbFormat = 'Y-m-d';

    public static string $formFormat = 'Y-m-d';


    public function __construct(
        public ?string $label = null,
        public bool $required = false,
        bool $hidden = false,
    ) {
        $this->hidden = $hidden;
    }

    public function getDbValue(): mixed
    {
        /** @var /DateTime $dt */
        $dt = parent::getDbValue();
        return $dt?->format(static::$dbFormat);
    }

    public function getFormValue(): mixed
    {
        /** @var /DateTime $dt */
        $dt = parent::getFormValue();
        return $dt?->format(static::$formFormat);
    }

    public function fromDbValue(mixed $value): mixed
    {
        return is_null($value) || '' === $value ? null : new \DateTime($value);
    }

    public function fromFormValue(mixed $value): mixed
    {
        return is_null($value) || '' === $value ? null : new \DateTime($value);
    }

    public function getFormAttributes(): array
    {
        $input = parent::getFormAttributes();
        $input['attributes']['type'] = 'date';

        return $input;
    }

    public function getOptions(): array
    {
        return [
        ];
    }
}
