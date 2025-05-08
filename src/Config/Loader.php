<?php

namespace Phntm\Lib\Config;

use League\Container\ContainerAwareInterface;
use League\Container\ContainerAwareTrait;
use Phntm\Lib\Config\Config;
use Phntm\Lib\Infra\Debug\Aware\DebugAwareInterface;
use Phntm\Lib\Infra\Debug\Aware\DebugAwareTrait;
use Symfony\Component\Yaml\Yaml;

class Loader implements ContainerAwareInterface, DebugAwareInterface
{
    use ContainerAwareTrait;
    use DebugAwareTrait;

    public function load(string $path): array
    {
        if (!file_exists($path)) {
            throw new \Error('Config file not found at ' . $path);
        }

        /*$this->debug()->startMeasure(
            'config_loader',
            'Loading config (' . pathinfo($path, PATHINFO_EXTENSION) . ')'
        );*/
        switch (pathinfo($path, PATHINFO_EXTENSION)) {
            case 'php':
                $config = require $path;
                break;
            case 'yaml':
            case 'yml':
                $config = $this->handleYaml($path);
                break;
            default:
                throw new \Error('Unsupported config file format: ' . $path);
        }
        //$this->debug()->stopMeasure('config_loader');

        return $config;
    }

    protected function handleYaml(string $path): array
    {
        $config = Yaml::parseFile($path);

        return $config;
    }
}
