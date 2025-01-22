<?php

namespace Phntm\Lib\Infra\Debug;
use DebugBar\DebugBar;
use DebugBar\StandardDebugBar;

class Debugger
{
    public static ?bool $enabled = null;

    private static DebugBar $bar;

    public static function init(): void
    {
        if (isset($_GET['debug']) && $_GET['debug'] !== 'false') {
            // set debug cookie
            setcookie('debug', 'true', [
                'expires' => time() + 3600,
                'path' => '/',
                'samesite' => 'Strict',
            ]);

            self::$enabled = true;
        } else if (isset($_COOKIE['debug']) && $_COOKIE['debug'] === 'true') {
            self::$enabled = true;
        }
        if (isset($_GET['debug']) && $_GET['debug'] === 'false') {
            // unset debug cookie
            setcookie('debug', 'false', [
                'expires' => time() - 3600,
                'path' => '/',
                'samesite' => 'Strict',
            ]);

            self::$enabled = false;
        }
    }

    public static function getBar(): ?DebugBar
    {
        if (!isset(self::$bar)) {
            self::$bar = new StandardDebugBar();
        }

        return self::$bar;
    }

    public static function startMeasure(string $name, string $label): void
    {
        self::getBar()['time']->startMeasure($name, $label);
    }

    public static function stopMeasure(string $name): void
    {
        self::getBar()['time']->stopMeasure($name);
    }

}
