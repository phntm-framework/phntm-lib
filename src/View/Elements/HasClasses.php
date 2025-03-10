<?php

namespace Phntm\Lib\View\Elements;

interface HasClasses
{
    public function withClass(string $class): self;
}
