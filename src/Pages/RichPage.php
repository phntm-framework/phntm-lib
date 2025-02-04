<?php

namespace Phntm\Lib\Pages;

use Nyholm\Psr7\Stream;
use Psr\Http\Message\StreamInterface;
use function dirname;

abstract class RichPage extends Renderable
{
    use Traits\Meta;

    protected bool $use_template = true;

    public function render(): StreamInterface
    {
        if (!$this->use_template) {
            return parent::render();
        }

        if (!isset($this->render_view)) {
            $pageLocation = dirname((new \ReflectionClass(static::class))->getFileName());
            $viewLocation = $pageLocation . '/view.twig';

            $this->twig->addView($viewLocation);
        } else {
            $this->twig->addView($this->render_view);
        }
        $body = $this->twig->renderTemplate(
            $this->getViewVariables()
        , $this->use_template);

        return Stream::create($body);
    }

    public function getViewVariables(): array
    {
        return [
            ...$this->view_variables,
            'phntm_meta' => $this->getMeta()
        ];
    }
}
