<?php

namespace Phntm\Lib\Pages;

interface SitemapInterface
{
    public function getSitemap(): array;

    public function getSitemapIndex(): array;

    public function getSitemapUrl(): string;

    public function getSitemapIndexUrl(): string;
}
