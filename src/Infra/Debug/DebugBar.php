<?php

namespace Phntm\Lib\Infra\Debug;

use DebugBar\DebugBar as ParentDebugBar;
use League\Container\ContainerAwareInterface;
use League\Container\ContainerAwareTrait;
use Phntm\Lib\Di\Container;

/**
 * Debug bar subclass which adds all included collectors
 */
class DebugBar extends ParentDebugBar implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    protected bool $isEnabled = false;

    public function __construct()
    {
        $collectors = func_get_args();
        foreach ($collectors as $collector) {
            $this->addCollector($collector);
        }

        $this->isEnabled = self::checkEligibility();
    }

    public static function checkEligibility(): bool
    {
        if (isset($_GET['debug']) && $_GET['debug'] === 'false') {
            // unset debug cookie
            setcookie('debug', 'false', [
                'expires' => time() - 3600,
                'path' => '/',
                'samesite' => 'Strict',
            ]);

            return false;
        }

        if (isset($_GET['debug']) && $_GET['debug'] !== 'false') {
            // set debug cookie
            setcookie('debug', 'true', [
                'expires' => time() + 3600,
                'path' => '/',
                'samesite' => 'Strict',
            ]);

            return true;
        } else if (isset($_COOKIE['debug']) && $_COOKIE['debug'] === 'true') {
            return true;
        }
        return false;
    }

    public function startMeasure(string $name, string $label = ''): void
    {
        $this->getCollector('time')->startMeasure($name, $label);
    }

    public function stopMeasure(string $name): void
    {
        $this->getCollector('time')->stopMeasure($name);
    }

    public function log($message, string $level='info'): void
    {
        if (!$this->enabled()) {
            return;
        }
        $this->getCollector('messages')->addMessage($message, $level);
    }

    public function enabled(): bool
    {
        return $this->isEnabled;
    }
}
