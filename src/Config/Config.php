<?php

namespace Phntm\Lib\Config;

use Dotenv\Dotenv;
use League\Container\ContainerAwareInterface;
use League\Container\ContainerAwareTrait;
use Symfony\Component\Cache\Adapter\RedisAdapter;

class Config implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    private array $config = [];
    private array $env = [];

    public function merge(array $new, ?array $old = null)
    {
        $return = true;
        if (null === $old) {
            $old = $this->config;
            $return = false;
        }

        foreach ($new as $key => $value) {
            if (is_array($value) && isset($old[$key]) && is_array($old[$key])) {
                if (array_is_list($value) && array_is_list($old[$key])) {
                    $old[$key] = [...$old[$key], ...$value];
                } else {
                    $old[$key] = $this->merge($value, $old[$key]);
                }
            } else {
                $old[$key] = $value;
            }
        }

        if ($return) {
            return $old;
        }

        $this->config = $old;
        return;
    }

    public function get(): array
    {
        return $this->config;
    }

    public function retrieve(string $key)
    {
        return $this->data_get($this->config, $key);
    }

    private function data_get($target, $key, $default = null)
    {
        if (is_null($key)) {
            return $target;
        }

        $key = is_array($key) ? $key : explode('.', $key);

        foreach ($key as $i => $segment) {
            unset($key[$i]);

            if (is_null($segment)) {
                return $target;
            }

            /*if ($segment === '*') {
                if (! is_iterable($target)) {
                    return $default instanceof \Closure ? $default() : $default;

                }

                $result = [];

                foreach ($target as $item) {
                    $result[] = static::data_get($item, $key);
                }

                return in_array('*', $key) ? Arr::collapse($result) : $result;
            }*/


            if (
                (is_array($target) || $target instanceof \ArrayAccess)
                && array_key_exists($segment, $target)
            ) {
                $target = $target[$segment];
            } elseif (is_object($target) && isset($target->{$segment})) {
                $target = $target->{$segment};
            } else {
                return $default instanceof \Closure ? $default() : $default;
            }
        }

        return $target;
    }

    public function isLoaded(): bool
    {
        return empty($this->config) === false;
    }

}
