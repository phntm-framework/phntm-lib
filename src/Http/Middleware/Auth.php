<?php

namespace Phntm\Lib\Http\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Phntm\Lib\Auth\Attributes\Auth as AuthAttribute;
use Phntm\Lib\Http\Redirect;
use Phntm\Lib\Infra\Debug\Debugger;
use Phntm\Lib\Auth\Pages\Login\Page as LoginPage;
use Phntm\Lib\Pages\PageInterface;

class Auth implements \Psr\Http\Server\MiddlewareInterface
{
    private array $roles = [];

    public const AUTH_COOKIE = 'pauth';

    /**
     * Route a request to a defined Page, or return a relevant status code.
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(
        \Psr\Http\Message\ServerRequestInterface $request, 
        \Psr\Http\Server\RequestHandlerInterface $handler
    ): \Psr\Http\Message\ResponseInterface {

        $page = $request->getAttribute('page', false);

        if (!$page instanceof PageInterface) {
            return $handler->handle($request);
        }

        $this->handleLogut($request);

        if ($this->isGuarded($page) && !$this->isAuthorized($request)) {
            Debugger::log('Unauthorized access to guarded page');
            $request = $request->withAttribute('page', new LoginPage());
            return $handler->handle($request->withAttribute('page', new LoginPage()));
        }

        return $handler->handle($request);
    }

    private function isAuthorized(\Psr\Http\Message\ServerRequestInterface $request): bool
    {
        if ($request->getParsedBody() && $request->getParsedBody()['password'] === 'admin') {
            $cookie = [
                'auth_id' => '1',
                'auth_roles' => 'admin|super',
            ];
            setcookie('pauth', json_encode($cookie), time() + 3600);
            throw new Redirect($request->getUri()->getPath(), 302);
        }

        if (!isset($request->getCookieParams()[self::AUTH_COOKIE])) {
            return false;
        }

        $cookie = json_decode($request->getCookieParams()[self::AUTH_COOKIE], true);

        if (!isset($cookie['auth_id']) || !isset($cookie['auth_roles'])) {
            return false;
        }

        if (count(array_intersect($this->roles, explode('|', $cookie['auth_roles']))) === 0) {
            return false;
        }

        return true;
    }

    private function isGuarded(PageInterface $page): bool
    {
        $reflection = new \ReflectionClass($page);
        $attributes = $reflection->getAttributes(AuthAttribute::class);
        if (empty($attributes)) {
            Debugger::log('No Auth attribute found');
            return false;
        }

        $roles = [];
        foreach ($attributes as $attribute) {
            $thisRoles = $attribute->getArguments();
            $thisRoles = is_array($thisRoles) ? $thisRoles : explode('|', $thisRoles);
            $roles = array_merge($roles, $thisRoles);
        }

        $roles = array_unique($roles);

        $this->roles = $roles;

        return true;
    }

    private function handleLogut(ServerRequestInterface $request): void
    {
        Debugger::log('Checking for logout query param');
        Debugger::log($request->getQueryParams());
        // get logout query param
        if (isset($request->getQueryParams()['logout'])) {
            Debugger::log('Logging out');
            setcookie(self::AUTH_COOKIE, '', time() - 3600);
            throw new Redirect($request->getHeader('referer')[0] ?? '/', 302);
        }
    }
}
