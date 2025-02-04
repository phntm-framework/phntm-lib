<?php

namespace Phntm\Lib\Model\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class DateTime extends Date
{
    public string $columnType = 'datetime';

    public static string $dbFormat = 'Y-m-d H:i:s';

    public static string $formFormat = 'Y-m-d\TH:i';

    public function getFormAttributes(): array
    {
        $input = parent::getFormAttributes();
        $input['attributes']['type'] = 'datetime-local';

        return $input;
    }
}
