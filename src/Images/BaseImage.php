<?php

namespace Phntm\Lib\Images;

use Closure;
use Phntm\Lib\Di\Container;
use Phntm\Lib\View\Elements\HasClasses;
use Spatie\Image\Drivers\ImageDriver;
use Stringable;
use Spatie\Image\Image;
use function dirname;

class BaseImage implements ImageInterface, Stringable, HasClasses
{
    protected static array $baseConfigurators = [];

    protected string $originalSource;
    protected string $source;
    protected string $location;
    protected string $alias;
    protected Image $configurator;
    protected string $hash;
    protected string $class = '';

    public function __construct(
        string $source, 
        string|bool $alias = false, 
        public string $alt = '',
        ?Closure $config = null,
    ) {
        $this->originalSource = $source;

        if (strpos($source, ROOT . '/images/') === 0) {
            $this->source = $source;
        } else {
            $this->source = ROOT . '/images/' . ltrim($source, '/');
        }

        if ($alias) {
            $this->location = ltrim($alias, '/');
        } else {
            $this->location = ltrim($this->source, ROOT . '/images/uploads/');
        }

        $this->configurator = Container::get()->get(ImageDriver::class);
        $this->configurator->loadFile($this->getSource());

        if (!is_null($config)) {
            $this->configurator = $config($this->configurator);
        }

        $this->validate();
    }

    protected function getSource(): string
    {
        return $this->source;
    }

    public function validate(): void
    {
        // if the public image does not exist, publish the source image
    }

    public function getSrc(): string
    {
        if (strpos($this->location, 'http') === 0) {
            return $this->location;
        }

        return '/images/' . $this->getHash() . '/' . $this->location;
    }

    public function get(): string
    {
        $this->generateIfNeeded();

        return "<img 
            src='{$this->getSrc()}' 
            alt='{$this->alt}' 
            width='{$this->configurator->getWidth()}'
            height='{$this->configurator->getHeight()}'
            class='{$this->class}'
        />";
    }

    public function getHash(): string
    {
        if (!isset($this->hash)) {
            // build a hash using the values of the image configuration
            $this->hash = md5($this->configurator->base64());
        }


        return $this->hash;
    }

    public function generateIfNeeded(): void
    {
        if (!file_exists($this->getPublicLocation())) {
            $this->makePublic();
        }
    }

    /**
     * Get the image as an HTML string
     * make modifications to the ->get() method
     *
     * @return string
     */
    final public function __toString(): string
    {
        return $this->get();
    }

    protected function makePublic(): void
    {
        if (!file_exists($this->getSource())) {
            throw new \Exception("Image not found: {$this->source}");
        }

        if (!is_dir(dirname($this->getPublicLocation()))) {
            mkdir(dirname($this->getPublicLocation()), 0777, true);
        }

        $this->configurator
            ->save($this->getPublicLocation());
    }

    public function getPublicLocation(): string
    {
        return ROOT . '/public' . $this->getPublicPath();
    }

    protected function getPublicPath(): string
    {
        return '/images/' . $this->getHash() . '/' . $this->location;
    }

    public function configure(Closure $callback): static
    {
        $this->configurator = $callback($this->configurator);

        return $this;
    }

    public function withAlt(string $alt): static
    {
        $this->alt = $alt;
        return $this;
    }

    public function withClass(string $class): static
    {
        $this->class = $class;
        return $this;
    }

    public function getOriginalSource(): string
    {
        return $this->originalSource;
    }

    public function getHeight(): int
    {
        return $this->configurator->getHeight();
    }

    public function getWidth(): int
    {
        return $this->configurator->getWidth();
    }
}
