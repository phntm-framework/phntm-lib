<?php

namespace Phntm\Lib\Pages;

use Nyholm\Psr7\Stream;
use Psr\Http\Message\StreamInterface;
use function dirname;

abstract class RichPage extends Renderable
{
    use Traits\Meta;

    public function render(): StreamInterface
    {
        if (!isset($this->render_view)) {
            $pageLocation = dirname((new \ReflectionClass(static::class))->getFileName());
            $viewLocation = $pageLocation . '/view.twig';

            $this->twig->addView($viewLocation);
        } else {
            $this->twig->addView($this->render_view);
        }
        return Stream::create(
            $this->twig->renderTemplate($this->getViewVariables())
        );
    }

    public function getViewVariables(): array
    {
        return [
            ...$this->view_variables,
            'this' => $this,
            'phntm_meta' => $this->getMeta()
        ];
    }
}
