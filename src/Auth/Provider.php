<?php

namespace Phntm\Lib\Auth;

use Phntm\Lib\Di\Container;

use Phntm\Lib\Di\ModuleProvider;

class Provider extends ModuleProvider
{
    public function definitions(): void
    {
        $this->getContainer()->add(GuardInterface::class, Guard::class);

    }
}
