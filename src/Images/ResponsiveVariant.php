<?php

namespace Phntm\Lib\Images;

use Spatie\Image\Drivers\ImageDriver;
use Spatie\Image\Enums\Fit;

class ResponsiveVariant extends BaseImage implements ImageVariantInterface
{
    protected string|int $descriptor;

    protected ResponsiveImage $parent;

    public function __construct(ResponsiveImage $parent, int $width)
    {
        $height = $width / $parent->getWidth() * $parent->getHeight();

        parent::__construct($parent->getOriginalSource(),
            config: fn(ImageDriver $config): ImageDriver => $config
                ->fit(Fit::Crop, $width, $height)
        );
        $this->descriptor = $width;
        $this->parent = $parent;
    }

    public function getParent(): static
    {
        return $this->parent;
    }
    public function getDescriptor(): string|int
    {
        return $this->descriptor;
    }

    public function getHash(): string
    {
        $parentHash = $this->parent->getHash();

        return $parentHash . '/' . parent::getHash();
    }
}
