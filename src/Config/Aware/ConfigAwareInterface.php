<?php

namespace Phntm\Lib\Config\Aware;

use Phntm\Lib\Config\Config;

interface ConfigAwareInterface
{
    public function config(): Config;

    public function setConfig(Config $config): static;
}
