<?php

namespace Pages\ForgotPassword;

use Phntm\Lib\Pages\RichPage;
use Phntm\Lib\Pages\Traits\HasManageUrl;

class Manage extends RichPage
{
    use HasManageUrl;

    public function __invoke(): void
    {
        $this->withScript('https://unpkg.com/@tailwindcss/browser@4');
    }
}
