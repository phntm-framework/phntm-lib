<?php

namespace Phntm\Lib\Model;

use Phntm\Lib\Model;
use Phntm\Lib\Model\Attribute as Col;

class Example extends Model
{
    protected static string $table = 'examples';

    #[Col\Text(label: 'Text input')]
    public string $text;

    #[Col\Text(label: 'Url slug', required: true)]
    public string $slug;

    #[Col\TextArea(label: 'Textarea', required: true)]
    public string $textarea;
}
