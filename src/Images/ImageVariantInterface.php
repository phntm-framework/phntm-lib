<?php

namespace Phntm\Lib\Images;

interface ImageVariantInterface
{
    public function getParent(): static;

    public function getDescriptor(): string|int;
}
