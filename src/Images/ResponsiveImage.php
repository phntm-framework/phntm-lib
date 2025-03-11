<?php

namespace Phntm\Lib\Images;

use Closure;

class ResponsiveImage extends BaseImage implements ResponsiveImageInterface
{
    private string $encodedName;
    private string $extension;
    private string $name;
    private array $srcset;
    private array $sizes;

    /** @var array<ResponsiveVariant> */
    private array $variants = [];

    /**
     * @param array<int> $sizes
     */
    public function srcset(array $widths): self
    {
        $this->validateSrcset($widths);
        $this->srcset = $widths;

        $this->generateVariants($widths);

        return $this;
    }

    public function validateSrcset(array $sizes): void
    {
    }

    /**
     * @param array<string|int, string> $sizes
     */
    public function sizes(array $sizes): self
    {
        $this->validateSizes($sizes);
        $this->sizes = $sizes;

        return $this;
    }

    public function validateSizes(array $sizes): void
    {
    }

    protected function checkGenerated(): void
    {
        if (empty($this->srcset) || empty($this->sizes)) {
            throw new \Exception('You must specify srcset and sizes before generating the image');
        }
    }

    /**
     * @param array<int> $sizes
     */
    protected function generateVariants(array $sizes): void
    {
        foreach ($sizes as $descriptor) {
            $variant = new ResponsiveVariant($this, $descriptor);
            $this->registerVariant($variant);
        }
    }

    public function getVariants(): array
    {
        return $this->variants;
    }

    public function registerVariant(ResponsiveVariant $variant): void
    {
        $this->variants[$variant->getDescriptor()] = $variant;
    }

    public function getSrcsetString(): string
    {
        $sources = [];
        foreach ($this->variants as $variant) {
            $variant->generateIfNeeded();
            $sources[] = $variant->getSrc() . ' ' . $variant->getDescriptor() . 'w';
        }
        return implode(', ', $sources);
    }

    public function getSizes(): string
    {
        return implode(', ', $this->sizes);
    }

    public function get(): string
    {
        $this->generateIfNeeded();

        return "<img 
            src='{$this->getSrc()}'
            srcset='{$this->getSrcsetString()}' 
            sizes='{$this->getSizes()}'
            alt='{$this->alt}' 
            width='{$this->configurator->getWidth()}'
            height='{$this->configurator->getHeight()}'
            class='{$this->class}'
        />";
    }

    public function configure(Closure $callback): static
    {
        if (!empty($this->variants)) {
            throw new \Exception('Cant configure after generating variants');
        }

        $this->configurator = $callback($this->configurator);

        return $this;
    }
}
