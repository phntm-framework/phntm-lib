<?php

namespace Phntm\Lib\View;

use DebugBar\Bridge\Twig\DebugTwigExtension;
use DebugBar\Bridge\Twig\DumpTwigExtension;
use DebugBar\Bridge\Twig\TimeableTwigExtensionProfiler;
use DebugBar\DataCollector\MessagesCollector;
use DebugBar\DataCollector\TimeDataCollector;
use League\Container\Argument\Literal\ArrayArgument;
use Phntm\Lib\Config\Config;
use Phntm\Lib\Di\ModuleProvider;
use Phntm\Lib\Di\Container;
use Phntm\Lib\Infra\Debug\DebugBar as PhntmDebugBar;
use Phntm\Lib\View\Twig\Extension;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;
use Symfony\UX\TwigComponent\Twig\ComponentExtension;
use Twig\Environment;
use Twig\Extension\DebugExtension;
use Twig\Loader\FilesystemLoader;
use Twig\Loader\LoaderInterface;
use Twig\Extra\Cache\CacheRuntime;
use Twig\Profiler\Profile;
use Twig\RuntimeLoader\ContainerRuntimeLoader;
use Twig\Template;

class Provider extends ModuleProvider
{
    public function provides(string $id): bool
    {
        $services = [
            LoaderInterface::class,
            Environment::class,
            CacheRuntime::class,
            TagAwareAdapter::class,
            ContainerRuntimeLoader::class,
            TemplateManager::class,
            Extension::class,
            ComponentExtension::class,
            DebugExtension::class,
            DumpTwigExtension::class,
            TimeableTwigExtensionProfiler::class,
            Profile::class,
        ];
        
        return in_array($id, $services);
    }

    public function definitions(): void
    {
        $this->getContainer()->addShared(TemplateManager::class)
            ->addArgument(Environment::class)
            ->addArgument(LoaderInterface::class)
        ;

        $twig = $this->getContainer()->addShared(Environment::class)
            ->addArgument(LoaderInterface::class)
            ->addArgument([
                'cache' => ROOT . '/tmp/cache/twig',
                'debug' => true,
                'strict_variables' => true,
            ])
            // add caching
            ->addMethodCall('addRuntimeLoader', [
                new class implements \Twig\RuntimeLoader\RuntimeLoaderInterface {
                    public function load($class) {
                        if (CacheRuntime::class === $class) {
                            return Container::get()->get(CacheRuntime::class);
                        }
                    }
                },
            ])
            ->addMethodCall('addExtension', [
                new \Twig\Extra\Cache\CacheExtension(),
            ])
            // container shit
            ->addMethodCall('addRuntimeLoader', [
                ContainerRuntimeLoader::class,
            ])
            // 
            ->addMethodCall('addExtension', [
                Extension::class,
            ])
        ;

        if (PhntmDebugBar::checkEligibility()) {
            $twig
                ->addMethodCall('enableDebug', [])
                ->addMethodCall('addExtension', [
                    DebugExtension::class,
                ])
                /*->addMethodCall('addExtension', [
                    TimeableTwigExtensionProfiler::class,
                ])*/
                ->addMethodCall('addExtension', [
                    DumpTwigExtension::class,
                ])
            ;

            $this->getContainer()->addShared(Profile::class);

            $this->getContainer()->addShared(TimeableTwigExtensionProfiler::class)
                ->addArgument(Profile::class)
                ->addArgument(TimeDataCollector::class)
            ;
            $this->getContainer()->addShared(DumpTwigExtension::class);
            $this->getContainer()->addShared(DebugExtension::class);
        }

        $this->getContainer()
            ->addShared(LoaderInterface::class, function (Config $config) {
                return new FilesystemLoader($config->retrieve('view.load_from'));
            })
            ->addArgument(Config::class)
        ;

        $this->getContainer()->add(CacheRuntime::class)
            ->addArgument(TagAwareAdapter::class)
        ;

        $this->getContainer()->add(TagAwareAdapter::class)
            ->addArgument(\Symfony\Component\Cache\Adapter\FilesystemAdapter::class)
        ;
        
        $this->getContainer()->add(ContainerRuntimeLoader::class)
            ->addArgument($this->getContainer())
        ;

        $this->getContainer()->add(Extension::class);

        /*$this->getContainer()->addShared(DebugTwigExtension::class)
            ->addArgument(MessagesCollector::class)
        ;*/
    }
}

