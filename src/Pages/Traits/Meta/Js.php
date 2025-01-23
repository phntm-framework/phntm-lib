<?php

namespace Phntm\Lib\Pages\Traits\Meta;

trait Js
{
    protected array $scripts = [ 'scripts' => [], 'inline_scripts' => [] ];

    protected function withScript(string $src, bool $inHead = true, bool $async = false, array $options = []): void
    {
        $script = [
            'src' => $src,
            'position' => $inHead ? 'head' : 'body',
            'async' => $async,
            ...$options,
        ];

        $this->scripts['scripts'][] = $script;
    }

    protected function withInlineScript(string $script): void
    {
        $this->scripts['inline_scripts'][] = $script;
    }

    protected function formattedScripts(): string
    {
        $rendered = '';

        foreach ($this->scripts['scripts'] as $script) {
            $src = $script['src'];
            $position = $script['position'];
            $async = $script['async'] ?? false ? 'async' : '';
            $defer = $script['defer'] ?? false ? 'defer' : '';

            $rendered .= <<<JS
            <script src="$src" $async $defer></script>
            JS;
        }

        foreach ($this->scripts['inline_scripts'] as $script) {
            $rendered .= <<<JS
            <script>$script</script>
            JS;
        }

        return $rendered;
    }
}
