<?php

namespace Phntm\Lib\Auth;

use JsonSerializable;

class Cookie implements \JsonSerializable
{
    protected static self $instance;

    private string $value;

    private function __construct(
        private string $name,
        array $value,
        private int $expire = 0,
        private string $path = '/',
        private string $domain = '',
        private bool $secure = false,
        private bool $httponly = false,
        private string $samesite = 'Lax'
    ) {
        $this->value = json_encode($value);
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public static function getFromGlobals(string $name): self
    {
        if (!isset(self::$instance)) {
            $value = $_COOKIE[$name] ?? '';
            self::$instance = new self($name, $value);
        }

        return self::$instance;
    }

    public static function set(
        array $value,
    ): void {

    }

    public function jsonSerialize(): array
    {
        return [
            'name' => $this->name,
            'value' => $this->value,
            'expire' => $this->expire,
            'path' => $this->path,
            'domain' => $this->domain,
            'secure' => $this->secure,
            'httponly' => $this->httponly,
            'samesite' => $this->samesite,
        ];
    }
}
