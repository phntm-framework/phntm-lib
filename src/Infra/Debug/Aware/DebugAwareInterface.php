<?php

namespace Phntm\Lib\Infra\Debug\Aware;

use Phntm\Lib\Infra\Debug\DebugBar;

interface DebugAwareInterface
{
    public function setDebugBar(DebugBar $debugBar): void;

    public function debug(): DebugBar;
}
