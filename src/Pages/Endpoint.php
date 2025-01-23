<?php

namespace Phntm\Lib\Pages;

use Symfony\Component\HttpFoundation\Request;

abstract class Endpoint implements PageInterface
{
    /**
     * @param array $dynamic_params
     */
    final public function __construct(
        protected array $dynamic_params = []
    ) {
    }

    abstract public function __invoke(Request $request): void;

    public function __get(string $name): mixed
    {
        if (array_key_exists($name, $this->dynamic_params)) {
            return $this->dynamic_params[$name];
        }
        return null;
    }
}
