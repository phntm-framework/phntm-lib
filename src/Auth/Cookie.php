<?php

namespace Phntm\Lib\Auth;

class Cookie
{
    public static Cookie $instance;

    public \stdClass $value;

    private function __construct(
        private string $name,
        string $value,
        private int $expire = 0,
        private string $path = '/',
        private string $domain = '',
        private bool $secure = false,
        private bool $httponly = false,
        private string $samesite = 'Lax'
    ) {
        $this->value = json_decode($value, false);
    }

    public static function getFromGlobals(string $name): self
    {
        if (!isset(self::$instance)) {
            $value = $_COOKIE[$name] ?? '';
            var_dump($value);
            exit;
        }

        return self::$instance;
    }
}
