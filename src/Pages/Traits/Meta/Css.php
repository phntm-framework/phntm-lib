<?php

namespace Phntm\Lib\Pages\Traits\Meta;

trait Css
{
    protected array $styles = [ 'link' => [], 'inline' => [] ];

    protected function withCss(string $href): void
    {
        $link = "<link rel=\"stylesheet\" href=\"$href\">";

        $this->styles['link'][] = $link;
    }

    protected function withInlineCss(string $css): void
    {
        $this->styles['inline'][] = $css;
    }

    protected function formattedStyles(): string
    {
        $rendered = '';

        foreach ($this->styles['link'] as $style) {
            $rendered .= $style;
        }

        foreach ($this->styles['inline'] as $style) {
            $rendered .= "<style>$style</style>";
        }

        return $rendered;
    }
}
