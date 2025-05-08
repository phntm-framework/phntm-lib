<?php

namespace Phntm\Lib\Config\Aware;

use Phntm\Lib\Config\Config;

trait ConfigAwareTrait
{
    private Config $config;

    public function config(): Config
    {
        if (!isset($this->config)) {
            throw new \RuntimeException('Config not set');
        }

        return $this->config;
    }

    public function setConfig(Config $config): static
    {
        $this->config = $config;

        return $this;
    }
}
