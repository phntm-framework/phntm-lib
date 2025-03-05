<?php

namespace Phntm\Lib\Images;

use Closure;

interface ImageInterface
{
    /**
     * Set the alt text for the image
     *
     * @param string $alt
     * @return ImageInterface
     */
    public function withAlt(string $alt): ImageInterface;

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
     * @return ImageInterface
     */
    public function configure(Closure $callback): ImageInterface;
}   
