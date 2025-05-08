<?php

namespace Phntm\Lib\Infra\Debug\Collectors;

use DebugBar\DataCollector\DataCollectorInterface;

class DummyCollector implements DataCollectorInterface
{
    public function collect(): array
    {
        return [];
    }

    public function getName(): string
    {
        return 'dummy';
    }

    public function __call($name, $arguments)
    {
        // Handle method calls dynamically
        return null;
    }
}
