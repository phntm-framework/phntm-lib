<?php

namespace Phntm\Lib\Site\Logo;

use Phntm\Lib\Images\ImageInterface;

interface ResolverInterface
{
    public function resolve(): ImageInterface;
}
