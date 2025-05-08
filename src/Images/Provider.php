<?php

namespace Phntm\Lib\Images;

use Spatie\Image\Drivers\ImageDriver;
use Spatie\Image\Enums\ImageDriver as DriverType;
use Phntm\Lib\Di\ModuleProvider;
use Spatie\Image\Image;

class Provider extends ModuleProvider
{
    public function provides(string $id): bool
    {
        $services = [
            ImageDriver::class,
        ];
        
        return in_array($id, $services);
    }

    public function definitions(): void
    {
        $this->getContainer()->add(ImageDriver::class, function () {
            return Image::useImageDriver(DriverType::Gd);
        });

    }
}
