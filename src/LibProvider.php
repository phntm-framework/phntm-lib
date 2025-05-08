<?php

namespace Phntm\Lib;

use Phntm\Lib\Di\ModuleProvider;

class LibProvider extends ModuleProvider
{
    public function provides(string $id): bool
    {
        $services = [
            //Resolver::class,
        ];
        
        return in_array($id, $services);
    }

    public function register(): void
    {
        //$this->getContainer()->add(Resolver::class);
    }
}

