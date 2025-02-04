<?php

namespace Phntm\Lib\Http\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Phntm\Lib\Auth\Attributes\Auth as AuthAttribute;
use Phntm\Lib\Http\Redirect;
use Phntm\Lib\Pages\Manageable;
use Phntm\Lib\Auth\Pages\Login\Page as LoginPage;
use Phntm\Lib\Pages\PageInterface;
use function array_unique;
use function gzdecode;
use function password_verify;
use function unserialize;

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

        if (!$this->isGuarded($page)) {
            return $handler->handle($request);
        }

        $this->handleLogin($request);

        if (!$this->isAuthorized($request)) {
            return $handler->handle($request->withAttribute('page', new LoginPage()));
        }

        return $handler->handle($request);
    }

    private function isAuthorized(\Psr\Http\Message\ServerRequestInterface $request): bool
    {
        if (!isset($request->getCookieParams()[self::AUTH_COOKIE])) {
            return false;
        }

        $cookie = $request->getCookieParams()[self::AUTH_COOKIE];
        $cookie = gzdecode($cookie);
        $cookie = unserialize($cookie);

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
        if ($page instanceof Manageable) {
            $this->roles = ['admin', 'super'];
            return true;
        }

        $reflection = new \ReflectionClass($page);
        $attributes = $reflection->getAttributes(AuthAttribute::class);

        if (empty($attributes)) {
            return false;
        }

        $roles = [];
        foreach ($attributes as $attribute) {
            $thisRoles = $attribute->getArguments();
            $thisRoles = is_array($thisRoles) ? $thisRoles : explode('|', $thisRoles);
            $roles = array_merge($roles, $thisRoles);
        }

        $this->roles = array_unique($roles);

        return true;
    }

    private function handleLogut(ServerRequestInterface $request): void
    {
        if (!isset($request->getQueryParams()['logout'])) {
            return;
        }

        setcookie(self::AUTH_COOKIE, '', time() - 3600);
        throw new Redirect($request->getUri()->getPath(), 302);
    }

    private function handleLogin(ServerRequestInterface $request): void
    {
        if ($request->getParsedBody() && isset($request->getParsedBody()['password'])) {
            $password = $request->getParsedBody()['password'];
            $username = $request->getParsedBody()['username'];

            $adminEntity = \Phntm\Lib\Model\Admin::find(1);

            if (!$adminEntity) {
                dd('Admin not found');
                return;
            }

            if (!password_verify($password, $adminEntity->password)) {
                return;
            }

            $cookie = [
                'auth_id' => $adminEntity->id,
                'auth_roles' => 'admin|super',
            ];

            $cookie = serialize($cookie);
            $cookie = gzencode($cookie);

            setcookie(self::AUTH_COOKIE, $cookie, [
                'expires' => time() + 3600,
                'path' => '/',
                'samesite' => 'Strict',
                'httponly' => true,
            ]);
            throw new Redirect($request->getUri()->getPath(), 302);
        }
    }
}
