<?php

namespace Phntm\Lib\Shared;

use Symfony\Component\Yaml\Yaml;
use function defined;
use function file_exists;

class Bootstrap 
{
    protected array $data;

    public function __construct()
    {
        if (!file_exists(ROOT . '/bootstrap.yml')) {
            throw new \Error('Bootstrap file not detected. Please create a bootstrap.yml file in the project root');
        }

        $this->data = Yaml::parseFile(ROOT . '/bootstrap.yml');
    }

    public function retrieve(string $key): mixed
    {
        return $this->data_get($this->data, $key);
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
}
