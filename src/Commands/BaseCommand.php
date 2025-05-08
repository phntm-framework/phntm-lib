<?php

namespace Phntm\Lib\Commands;

use League\Container\ContainerAwareInterface;
use League\Container\ContainerAwareTrait;
use Phntm\Lib\Config\Aware\ConfigAwareInterface;
use Phntm\Lib\Config\Aware\ConfigAwareTrait;
use Phntm\Lib\Di\ContainerAware;
use Phntm\Lib\Infra\Debug\Aware\DebugAwareInterface;
use Phntm\Lib\Infra\Debug\Aware\DebugAwareTrait;
use Symfony\Component\Console\Command\Command;

class BaseCommand extends Command implements
    ContainerAwareInterface,
    ConfigAwareInterface,
    DebugAwareInterface
{
    use ContainerAwareTrait;
    use ConfigAwareTrait;
    use DebugAwareTrait;
}
