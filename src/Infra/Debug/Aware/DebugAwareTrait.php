<?php

namespace Phntm\Lib\Infra\Debug\Aware;

use Phntm\Lib\Infra\Debug\DebugBar;
use DebugBar\DebugBar as BaseDebugBar;
use Phntm\Lib\Infra\Debug\DummyBar;

trait DebugAwareTrait
{
    protected DebugBar $debugBar;

    public function setDebugBar(DebugBar $debugBar): void
    {
        $this->debugBar = $debugBar;
    }

    public function debug(): DebugBar
    {
        if (!isset($this->debugBar)) {
            return new DummyBar();
        }

        return $this->debugBar;
    }
}
