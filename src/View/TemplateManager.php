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
            $this->loader->addPath($view_directory);
            // update the environment with the new loader
            $this->environment->setLoader($this->loader);
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
            $this->loader->addPath($template_directory);
            // update the environment with the new loader
            $this->environment->setLoader($this->loader);
        } catch (\Throwable $e) {
            dump($e);
            exit;
        }

    }

    public function renderTemplate(array $data, bool $use_document = true): string
    {
        try {
            $meta = $data['phntm_meta'];
            unset($data['phntm_meta']);

            $view = $this->environment->render($this->view_file, $data);

            if ($use_document === false) {
                return $view;
            }

            $document = $this->environment->render($this->template_file, [
                'head' => $meta['head'] ?? '',
                'body_class' => $meta['body_class'] ?? '',
                'view' => $view,
            ]);

            return $document;

        } catch (\Twig\Error\Error $e) {
            throw $e;
        }
    }
}
