<?php

namespace Phntm\Lib\Di;

use League\Container\ServiceProvider\AbstractServiceProvider;

abstract class ModuleProvider extends AbstractServiceProvider
{
    public static string $config = '';

    protected array $timing = [];

    public static function getConfigFile(): string
    {
        return static::$config;
    }

    public function register(): void
    {
        $this->timing['label'] = static::class;
        $this->timing['start'] = microtime(true);
        $this->definitions();
        $this->timing['end'] = microtime(true);
    }

    abstract public function definitions(): void;

    public function getTiming(): array
    {
        return $this->timing;
    }
}
