<?php

namespace Phntm\Lib\Images;

interface ResponsiveImageInterface extends ImageInterface
{
    public function getVariants(): array;

    public function registerVariant(ResponsiveVariant $variant): void;
}
