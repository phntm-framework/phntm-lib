<?php

namespace Phntm\Lib\Model\Attribute\Traits;

use Closure;

trait HasHooks
{
    public array $hooks = [];

    /**
     * Register a hook for a specific event
     *
     * callbacks take the form of `function($value, $attribute): void {}`
     *
     * @param string $hook
     * @param string|callable|Closure $callback
     */
    public function registerHook(string $hook, string|callable|Closure $callback): void
    {
        $this->hooks[$hook][] = $callback;
    }

    public function hasHook(string $hook): bool
    {
        return isset($this->hooks[$hook]);
    }

    public function triggerHook(string $hook): void
    {
        if (!isset($this->hooks[$hook])) {
            return;
        }

        foreach ($this->hooks[$hook] as $callback) {
            if (is_string($callback)) {
                $callback = [$this, $callback];
            }

            if (is_callable($callback)) {
                $callback($this->model->{$this->columnName}, $this);
            }
        }
    }
}
