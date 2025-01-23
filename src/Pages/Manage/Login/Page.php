<?php

namespace Phntm\Lib\Pages\Manage\Login;

use Phntm\Lib\Pages\AbstractPage;
use Symfony\Component\HttpFoundation\Request;


class Page extends AbstractPage
{
    public string $body_class = 'home';
    
    public function __invoke(Request $request): void
    {
        $this->title('Login');

        $this->withScript('https://cdn.tailwindcss.com');
    }
}
