<?php

namespace Phntm\Lib\Pages\Traits;

trait Meta
{
    use Meta\Js;
    use Meta\Css;

    public string $body_class = '';

    protected array $body_classes = [];

    protected string $title = '';

    protected function title(string $title): void
    {
        $this->title = $title;
    }

    protected  function head(): string
    {
        // page title
        $head = "<title>{$this->title}</title>";


        // javascript
        $head .= $this->formattedScripts();

        // styles
        $head .= $this->formattedStyles();

        return $head;
    }

    protected function withBodyClass(string $class): void
    {
        if (!in_array($class, $this->body_classes)) {
            $this->body_classes[] = $class;
        }
    }

    protected function bodyClasses(): string
    {
        return implode(' ', [ $this->body_class, ...$this->body_classes ]);
    }

    final public function getMeta(): array
    {
        return [
            'head' => $this->head(),
            'body_class' => $this->bodyClasses(),
        ];
    }
}
