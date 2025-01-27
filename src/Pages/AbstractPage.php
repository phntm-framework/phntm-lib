<?php

namespace Phntm\Lib\Pages;

use Phntm\Lib\Infra\Debug\Debugger;
use Phntm\Lib\View\TemplateManager;
use Nyholm\Psr7\Stream;
use Psr\Http\Message\StreamInterface;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractPage implements PageInterface
{
    use Traits\Meta;

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

    /**
     * AbstractPage constructor.
     *
     * @param array $dynamic_params
     */
    public function __construct(protected array $dynamic_params = [])
    {
        try {
            if (!isset($this->render_template)) {
                $template = PHNTM . 'views/html.twig';

                if ($this instanceof Manageable) {
                    $template = PHNTM . 'views/manage-html.twig';
                }
            } else {
                $template = $this->render_template;
            }

            $this->twig = new TemplateManager($template);
        } catch (\Throwable $e) {
            dump($e);
            exit;
        }
    }

    abstract public function __invoke(Request $request): void;

    public function render(Request $request): StreamInterface
    {
        $this($request);

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

    final public function renderWith(array $variables): void
    {
        $this->view_variables = array_merge($this->view_variables, $variables);
    }

    final public static function getManageClassName(): string
    {
        return substr(static::class, 0, -4) . 'Manage';
    }
}
