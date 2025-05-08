<?php

namespace Phntm\Lib\Di;


use League\Container\DefinitionContainerInterface;

trait ContainerAware
{
    protected function getContainer(): DefinitionContainerInterface
    {
        return Container::get();
    }
}
