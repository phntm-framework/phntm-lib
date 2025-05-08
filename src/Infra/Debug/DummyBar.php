<?php

namespace Phntm\Lib\Infra\Debug;

class DummyBar extends DebugBar
{
    public function __construct()
    {
        // Dummy constructor
    }

    public static function checkEligibility(): bool
    {
        return false; // Always return false for the dummy bar
    }

    public function enabled(): bool
    {
        return false; // Always return false for the dummy bar
    }

    public function addCollector($collector): void
    {
        // Dummy method to add a collector
    }

    public function getCollectors(): array
    {
        return []; // Return an empty array for the dummy bar
    }

    public function getCollector($name)
    {
        return new Collectors\DummyCollector(); // Return a dummy collector
    }

}
