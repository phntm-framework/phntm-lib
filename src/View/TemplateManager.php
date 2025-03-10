<?php

namespace Phntm\Lib\View;

use Phntm\Lib\Infra\Debug\Debugger;

class TemplateManager 
{
    protected \Twig\Environment $environment;

    protected \Twig\Loader\FilesystemLoader $loader;

    protected string $template_file;

    protected string $view_file;

    public function __construct(string $template_location)
    {
        $template_directory = ROOT . '/' . dirname($template_location);
        $this->template_file = basename($template_location);

        $this->loader = new \Twig\Loader\FilesystemLoader([
            $template_directory,
        ]);

        $this->environment = new \Twig\Environment($this->loader, [
            'cache' => ROOT . '/tmp/cache/twig',
            'debug' => true,
            'strict_variables' => true,
        ]);

        $this->environment->addExtension(new Twig\Extension());

        if (Debugger::$enabled) {
            $this->environment->addExtension(new \Twig\Extension\DebugExtension());
            $profile = new \Twig\Profiler\Profile();
            $this->environment->addExtension(
                new \DebugBar\Bridge\Twig\TimeableTwigExtensionProfiler($profile, Debugger::getBar()['time'])
            );

            $this->environment->enableDebug();
            $this->environment->addExtension(new \DebugBar\Bridge\Twig\DumpTwigExtension());
            $this->environment->addExtension(new \DebugBar\Bridge\Twig\DebugTwigExtension(Debugger::getBar()['messages']));

            if (!Debugger::getBar()->hasCollector('twig')) {
                Debugger::getBar()->addCollector(new \DebugBar\Bridge\NamespacedTwigProfileCollector($profile, $this->environment));
            }
        }
    }

    public function addView(string $view_location): void
    {
        $this->view_file = basename($view_location);
        $view_directory = dirname($view_location);

        try {
            $this->addViewPath($view_directory);
        } catch (\Throwable $e) {
            dump($e);
            exit;
        }
    }

    public function addTemplate(string $template_location): void
    {
        $this->template_file = basename($template_location);
        $template_directory = dirname($template_location);

        try {
            $this->addViewPath($template_directory);
        } catch (\Throwable $e) {
            dump($e);
            exit;
        }

    }

    public function addViewPath(string $view_path): void
    {
        $this->loader->addPath($view_path);
        // update the environment with the new loader
        $this->environment->setLoader($this->loader);
    }

    public function renderTemplate(array $data): string
    {
        try {
            $view = $this->environment->load($this->view_file)->render($data);

            return $view;
        } catch (\Twig\Error\Error $e) {
            throw $e;
        }
    }

    public function environment(): \Twig\Environment
    {
        return $this->environment;
    }
}
