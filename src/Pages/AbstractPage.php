<?php

namespace Phntm\Lib\Pages;

use Phntm\Lib\Infra\Debug\Debugger;
use Phntm\Lib\View\TemplateManager;
use Nyholm\Psr7\Stream;
use Psr\Http\Message\StreamInterface;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractPage extends Renderable
{
    use Traits\Meta;

    public function render(): StreamInterface
    {
        $pageDirectory = dirname((new \ReflectionClass(static::class))->getFileName());

        if (!isset($this->render_view)) {

            // render_view located in the same directory as the page class
            $this->render_view = $pageDirectory . '/view.twig';
            if ($this instanceof Manageable) {
                $this->render_view = $pageDirectory . '/manage-form.twig';
            }
            $this->full_render_view = $this->render_view;

        } elseif (file_exists($pageDirectory . '/' . $this->render_view)) {

            // render_view is a relative path from the page class
            $this->full_render_view = $pageDirectory . '/' . $this->render_view;

        } elseif (file_exists(PAGES . $this->render_view)) {

            // render_view is a relative path from the PAGES directory
            $this->full_render_view = PAGES . $this->render_view;

        } elseif (file_exists(ROOT . $this->render_view)) {

            // render_view is a full path from the root of the project
            $this->full_render_view = ROOT . $this->render_view;

        } else {
            // no view file found
            $this->withContentType('text/html');
            return Stream::create('');
        }
        Debugger::startMeasure('page_render', 'Rendering');

        $this->twig->addView($this->full_render_view);

        $body = $this->twig->renderTemplate([
            ...$this->view_variables, 
            'phntm_meta' => $this->getMeta()
        ], $this->use_template);

        Debugger::stopMeasure('page_render');

        return Stream::create($body);
    }

    public function __get(string $name): mixed
    {
        if (array_key_exists($name, $this->dynamic_params)) {
            return $this->dynamic_params[$name];
        }
        return null;
    }

    final public static function getManageClassName(): string
    {
        return substr(static::class, 0, -4) . 'Manage';
    }
}
