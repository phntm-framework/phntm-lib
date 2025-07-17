<?php

namespace Phntm\Lib\Auth;

use Psr\Http\Message\ServerRequestInterface;

class Guard implements GuardInterface
{
    public const AUTH_COOKIE = 'pauth';

    public array $roles = [];

    public function __construct(
        protected ServerRequestInterface $request,
    ) {
    }

    public function isGuarded(): bool
    {
        if (!isset($this->request->getCookieParams()[self::AUTH_COOKIE])) {
            return false;
        }

        $cookie = $this->request->getCookieParams()[self::AUTH_COOKIE];
        $cookie = gzdecode($cookie);
        $cookie = unserialize($cookie);

        if (!isset($cookie['auth_id']) || !isset($cookie['auth_roles'])) {
            return false;
        }

        // if (count(array_intersect($this->roles, explode('|', $cookie['auth_roles']))) === 0) {
            //dd(array_intersect($this->roles, explode('|', $cookie['auth_roles'])));
            // return false;
        // }

        return true;
    }
}
