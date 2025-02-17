<?php

namespace Phntm\Lib\Images;

class Responsive
{
    private string $encodedName;
    private string $extension;
    private string $name;
    private array $setset;
    private array $sizes;

    public function __construct(
        private string $location,
        private bool $mobileFirstSizes = true
    ) {
        $this->extension = pathinfo($location, PATHINFO_EXTENSION);
        $this->name = pathinfo($location, PATHINFO_FILENAME);
    }

    /**
     * @param array<int> $sizes
     */
    public function srcset(array $sizes): self
    {
        $this->validateSrcset($sizes);
        $this->setset = $sizes;

        return $this;
    }

    /**
     * @param array<string|int, string> $sizes
     */
    public function sizes(array $sizes): string
    {
        $this->validateSizes($sizes);
        $this->sizes = $sizes;

        return $this;
    }

    public function get(): string
    {
        $this->checkGenerated();
        $this->encodedName = $this->name . '.' . $this->extension;
        $srcset = $this->generateSrcset();
        $sizes = $this->generateSizes();
        return "<img src='{$this->encodedName}' srcset='{$srcset}' sizes='{$sizes}' />";
    }

    protected function checkGenerated(): void
    {
        if (empty($this->setset) || empty($this->sizes)) {
            throw new \Exception('You must specify srcset and sizes before generating the image');
        }


    }
}
