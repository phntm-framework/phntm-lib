<?php

namespace Phntm\Lib\View;

use Twig\Environment;
use Twig\Loader\LoaderInterface;
use Twig\Runtime\EscaperRuntime;

class TemplateManager 
{
    protected string $view_file;

    public function __construct(
        protected Environment $environment,
        protected LoaderInterface $loader,
    ) {
        $this->environment->getRuntime(EscaperRuntime::class)
            ->addSafeClass(\Phntm\Lib\Images\ImageInterface::class, ['html'])
        ;
    }

    public function addView(string $view_location): void
    {
        $this->view_file = basename($view_location);
        $view_directory = dirname($view_location);

        $this->loader->addPath($view_directory);
    }

    public function addViewPath(string $view_path): void
    {
        $this->loader->addPath($view_path);
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
