<?php

namespace Phntm\Lib\Model;

use Phntm\Lib\Model;
use Phntm\Lib\Model\Attribute as Col;

class Admin extends Model
{
    #[Col\Text(label: 'Username', required: true)]
    public string $username;

    #[Col\Text(label: 'Password', required: true)]
    public string $password;
}
