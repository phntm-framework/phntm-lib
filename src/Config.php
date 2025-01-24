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
}
