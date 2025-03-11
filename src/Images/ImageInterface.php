<?php

namespace Phntm\Lib\Images;

use Closure;

interface ImageInterface
{
    /**
     * Set the alt text for the image
     *
     * @param string $alt
     * @return static
     */
    public function withAlt(string $alt): static;

    /**
     * Get the image as an HTML string
     *
     * @return string
     */
    public function get(): string;

    /**
     * Get the public URL of the image
     *
     * @return string
     */
    public function getSrc(): string;

    /**
     * Ensure the state of the image on the server is as expected   
     *
     * @return void
     */
    public function validate(): void;

    /**
     * apply the configuration to the image
     *
     * @return static
     */
    public function configure(Closure $callback): static;

    /**
     * Get the width of the image
     *
     * @return int
     */
    public function getWidth(): int;

    /**
     * Get the height of the image
     *
     * @return int
     */
    public function getHeight(): int;
}   
