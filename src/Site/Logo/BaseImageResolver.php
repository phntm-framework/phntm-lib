<?php

namespace Phntm\Lib\Site\Logo;

use Phntm\Lib\Images\BaseImage;

class BaseImageResolver implements ResolverInterface
{
    public function resolve(): BaseImage
    {
        return new BaseImage('');
    }
}
