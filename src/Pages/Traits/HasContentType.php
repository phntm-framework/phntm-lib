<?php

namespace Phntm\Lib\Pages\Traits;

trait HasContentType
{
    protected string $contentType = 'text/html';

    final protected function withContentType(string $type): self
    {
        $this->contentType = $type;

        return $this;
    }

    final public function getContentType(): string
    {
        return $this->contentType;
    }
}

