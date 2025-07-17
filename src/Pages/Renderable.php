<?php

namespace Phntm\Lib\Pages;

use Nyholm\Psr7\Stream;
use Phntm\Lib\View\TemplateManager;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\ServerRequestInterface as PsrRequest;

abstract class Renderable extends Endpoint implements CanRender
{
    protected TemplateManager $twig;

    protected array $view_variables = [];

    protected bool $use_template = true;

    /**
     * Template used for rendering whole document
     */
    protected string|false|null $render_template = null;

    /**
     * View used to render page content
     */
    protected string|false|null $render_view = null;

    protected string $full_render_view;

    public function dispatch(PsrRequest $request): StreamInterface
    {
        $this->setRequest($request);
        $this->twig = $this->getContainer()->get(TemplateManager::class);

        $this->debug()->startMeasure('page-invoke', 'Page invoke');
        $this();
        $this->debug()->stopMeasure('page-invoke');

        $this->preRender();
        return Stream::create($this->render());
    }

    public function preRender(): void
    {
    }

    public function render(): StreamInterface
    {
        $pageLocation = dirname((new \ReflectionClass(static::class))->getFileName());
        $classname = (new \ReflectionClass(static::class))->getShortName();
        $viewLocation = str_replace($classname, 'view.twig', $pageLocation);
        dd($pageLocation, $viewLocation);

        $this->twig->addView($viewLocation);

        return Stream::create(
            $this->twig->environment()->render('view.twig', $this->getViewVariables())
        );
    }

    public function getViewVariables(): array
    {
        return $this->view_variables;
    }

    final public function renderWith(array $variables): void
    {
        $this->view_variables = array_merge($this->view_variables, $variables);
    }
}
