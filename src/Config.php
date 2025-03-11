<?php

namespace Phntm\Lib;

use function array_is_list;

class Config
{
    private static array $config = [];

    /**
     * merges the new config with the existing, overwriting named keys, and merging numeric keys
     */
    public static function merge(array $new, ?array $old = null)
    {
        $return = true;
        if (null === $old) {
            $old = self::$config;
            $return = false;
        }

        foreach ($new as $key => $value) {
            if (is_array($value) && isset($old[$key]) && is_array($old[$key])) {
                if (array_is_list($value) && array_is_list($old[$key])) {
                    $old[$key] = [...$old[$key], ...$value];
                } else {
                    $old[$key] = self::merge($value, $old[$key]);
                }
            } else {
                $old[$key] = $value;
            }
        }

        if ($return) {
            return $old;
        }

        self::$config = $old;
        return;
    }

    public static function get(): array
    {
        return self::$config;
    }

    public static function retrieve(string $key)
    {
        return self::data_get(self::$config, $key);
    }

    private static function data_get($target, $key, $default = null)
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

    public static function env(string $key): mixed
    {
        return $_ENV[$key] ?? null;
    }

}
