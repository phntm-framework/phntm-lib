<?php

namespace Phntm\Lib\Pages\Manage;

use Bchubbweb\PhntmFramework\Pages\AbstractPage;

abstract class Page extends AbstractPage
{
    public function isAuthorized(): bool
    {
        return isset($_GET['allow']);
    }
}
