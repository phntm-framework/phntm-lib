<?php

namespace Phntm\Lib\Pages\Traits;

use Phntm\Lib\Infra\Routing\Attributes\Action;
use Phntm\Lib\Infra\Routing\Attributes\Dynamic;
use Phntm\Lib\Infra\Routing\Router;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use function rtrim;

trait HasActions
{
    final public function __invoke(Request $request): void
    {
        if (static::hasAction($this->matchedAction)) {
            $this->{$this->matchedAction}($request);
        }
    }

    public static function hasAction(string $action): bool
    {
        return array_key_exists($action, static::getActions());
    }

    /**
     * Returns an array of methods tagged with the #[Action] attribute
     *
     * @return array
     */
    public static function getActions(): array
    {
        $reflection = new \ReflectionClass(static::class);

        $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
        $actions = [];
        foreach ($methods as $method) {
            if (!$method->getAttributes(Action::class)) {
                continue;
            }

            $actions[$method->getName()] = $method->getAttributes(Action::class)[0]->newInstance();
        }

        return $actions;
    }

    public static function registerRoutes(RouteCollection $routes): void
    {

        $baseRoute = static::resolveBaseRoute();

        $actions = static::getActions();

        foreach ($actions as $method => $action) {
            $path = rtrim($baseRoute['path'],'/manage') . '/' . ltrim($action->slug, '/');
            $path = rtrim($path, '/');

            $routes->add(
                name: $baseRoute['name'] . '::' . $method,
                route: new Route(Dynamic::getTypeSafePath($path)),
                priority: Router::calcRoutePriority(Dynamic::getTypeSafePath($path))
            );
        }
    }
}
