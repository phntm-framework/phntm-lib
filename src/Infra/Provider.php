<?php

namespace Phntm\Lib\Infra;

use DebugBar\Bridge\NamespacedTwigProfileCollector;
use DebugBar\DataCollector\ExceptionsCollector;
use DebugBar\DataCollector\MemoryCollector;
use DebugBar\DataCollector\MessagesCollector;
use DebugBar\DataCollector\PhpInfoCollector;
use DebugBar\DataCollector\TimeDataCollector;
use DebugBar\DataCollector\RequestDataCollector;
use Phntm\Lib\Di\ModuleProvider;
use DebugBar\DebugBar;
use Phntm\Lib\Infra\Debug\DebugBar as PhntmDebugBar;

class Provider extends ModuleProvider
{
    public function provides(string $id): bool
    {
        $services = [
            DebugBar::class,
            PhpInfoCollector::class,
            MessagesCollector::class,
            RequestDataCollector::class,
            TimeDataCollector::class,
            MemoryCollector::class,
            ExceptionsCollector::class,
            NamespacedTwigProfileCollector::class,
        ];
        
        return in_array($id, $services);
    }

    public function definitions(): void
    {
        if (PhntmDebugBar::checkEligibility()) {
            $this->getContainer()->addShared(DebugBar::class, PhntmDebugBar::class)
                ->addArgument(PhpInfoCollector::class)
                ->addArgument(MessagesCollector::class)
                ->addArgument(RequestDataCollector::class)
                ->addArgument(TimeDataCollector::class)
                ->addArgument(MemoryCollector::class)
                ->addArgument(ExceptionsCollector::class)
                //->addArgument(NamespacedTwigProfileCollector::class)
            ;

            $this->getContainer()->addShared(PhpInfoCollector::class);
            $this->getContainer()->addShared(MessagesCollector::class)
                //->addMethodCall('useHtmlVarDumper', [false])
            ;
            $this->getContainer()->addShared(RequestDataCollector::class);
            $this->getContainer()->addShared(TimeDataCollector::class);
            $this->getContainer()->addShared(MemoryCollector::class);
            $this->getContainer()->addShared(ExceptionsCollector::class);
            $this->getContainer()->addShared(NamespacedTwigProfileCollector::class)
                ->addArgument(\Twig\Profiler\Profile::class)
                ->addArgument(\Twig\Environment::class)
            ;
        } else {
            $this->getContainer()->addShared(DebugBar::class, Debug\DummyBar::class)
            ;
        }

    }
}

