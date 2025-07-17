<?php

namespace Phntm\Lib\Auth;

interface GuardInterface
{
    public function isGuarded(): bool;
}
