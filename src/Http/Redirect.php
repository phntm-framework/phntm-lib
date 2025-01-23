<?php

namespace Phntm\Lib\Http;

class Redirect extends \Exception
{
    public function __toString()
    {
        return 'Location: ' . $this->message;
    }
}
