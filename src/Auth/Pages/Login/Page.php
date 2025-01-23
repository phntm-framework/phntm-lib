<?php

namespace Phntm\Lib\Auth\Pages\Login;

use Phntm\Lib\Pages\AbstractPage;
use Symfony\Component\HttpFoundation\Request;


class Page extends AbstractPage
{
    public string $body_class = 'min-h-screen bg-gray-100 py-12 px-2 sm:px-6 lg:px-8 flex justify-center items-center';

    public function __invoke(Request $request): void
    {
        $this->title('Login');

        $this->withScript('https://unpkg.com/@tailwindcss/browser@4');
    }
}
