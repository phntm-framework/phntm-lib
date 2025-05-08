<?php

namespace Phntm\Lib\Routing\Cache;

use Phntm\Lib\Infra\Debug\Aware\DebugAwareInterface;
use Phntm\Lib\Infra\Debug\Aware\DebugAwareTrait;
use Symfony\Component\Routing\Matcher\Dumper\CompiledUrlMatcherDumper;
use Symfony\Component\Routing\RouteCollection;

class FileCache implements RouteCacheInterface, DebugAwareInterface
{
    use DebugAwareTrait;

    private const CACHE_FILE = ROOT . '/tmp/cache/routes.php';

    public function get(): ?array
    {
        $this->debug()->startMeasure('route.cache.get', 'Get Routes from File');
        $routes = require self::CACHE_FILE;
        $this->debug()->stopMeasure('route.cache.get');

        return $routes;
    }

    public function set(RouteCollection $routes): bool
    {
        $compiledRoutes = (new CompiledUrlMatcherDumper($routes))->getCompiledRoutes();

        $fileDeleted = false;

        if (!($filePath = tempnam(ROOT . "/tmp/cache", "temp-phntm-routes-"))) {
            return false;
        }

        try {
            if (!file_put_contents($filePath, "<?php\nreturn " . var_export($compiledRoutes, true) . ";\n")) {
                throw new \RuntimeException("Failed to save features to temporary file");
            }

            if (!rename($filePath, self::CACHE_FILE)) {
                throw new \RuntimeException("Failed to rename temporary file");
            }
        } catch (\RuntimeException $e) {
            if (file_exists($filePath)) {
                unlink($filePath);
                $fileDeleted = true;
            }

            return false;

        } finally {
            if (file_exists($filePath) && !$fileDeleted) {
                unlink($filePath);
                return true;
            }
        }

        return false;
    }

    public function clear(): bool
    {
        return true;
    }

}
