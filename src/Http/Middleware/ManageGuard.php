<?php

namespace Phntm\Lib\Http\Middleware;

use Phntm\Lib\Infra\Debug\Debugger;
use Phntm\Lib\Pages\Manage\Login\Page;
use Phntm\Lib\Pages\Manage\Page as ManagePage;

class ManageGuard implements \Psr\Http\Server\MiddlewareInterface
{
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

        if (!$request->getAttribute('page', false)) {
            return $handler->handle($request);
        }

        $page = $request->getAttribute('page');

        if (is_a($page, ManagePage::class) && !$this->isAuthorized($request)) {
            Debugger::log('ManageGuard: Unauthorized access to ManagePage');
            $request = $request->withAttribute('page', new Page());
            return $handler->handle($request->withAttribute('page', new Page()));
        }

        return $handler->handle($request);
    }

    private function isAuthorized(\Psr\Http\Message\ServerRequestInterface $request): bool
    {
        if ($request->getParsedBody() && $request->getParsedBody()['password'] === 'admin') {
            setcookie('phntm_admin', '1', time() + 3600);
            header('Location: ' . $request->getUri()->getPath());
            return false;
        }

        return isset($request->getCookieParams()['phntm_admin']) && $request->getCookieParams()['phntm_admin'] === '1';
    }
}
