<?php

namespace Phntm\Lib\Http;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class Request extends SymfonyRequest implements ServerRequestInterface
{
    public function withProtocolVersion(string $version): MessageInterface
    {
    }

    public function getHeaders(): array
    {
        return $this->headers->all();
    }

    public function hasHeader(string $name): bool
    {
        return $this->headers->has($name);
    }

    public function getHeader(string $name): array
    {
        return $this->headers->get($name);
    }

    public function getHeaderLine(string $name): string
    {
        return $this->headers->get($name, '');
    }

    public function withHeader(string $name, $value): MessageInterface
    {
        $request = clone $this;
        $request->headers->set($name, $value);
        return $request;
    }

    public function withAddedHeader(string $name, $value): MessageInterface
    {
        $request = clone $this;
        $request->headers->add([$name => $value]);
        return $request;
    }

    public function withoutHeader(string $name): MessageInterface
    {
        $request = clone $this;
        $request->headers->remove($name);
        return $request;
    }

    public function getBody(): StreamInterface
    {
        return $this->getContent();
    }

    public function withBody(StreamInterface $body): MessageInterface
    {
        $request = new Request(
            $this->getQueryParams(),
            $this->getParsedBody(),
            $this->getAttributes(),
            $this->getCookieParams(),
            $this->getUploadedFiles(),
            $this->getServerParams(),
            $body,
        );
        
        return $request;
    }

    public function getRequestTarget(): string
    {
        return $this->getPathInfo();
    }

    public function withRequestTarget(string $requestTarget): RequestInterface
    {
        $request = clone $this;
        $request->server->set('REQUEST_URI', $requestTarget);
        return $request;
    }

    public function withMethod(string $method): RequestInterface
    {
        $request = clone $this;
        $request->setMethod($method);
        return $request;
    }

    public function withUri(UriInterface $uri, bool $preserveHost = false): RequestInterface
    {
        $request = clone $this;
        $request->server->set('REQUEST_URI', (string)$uri);
        if ($preserveHost) {
            $request->headers->set('Host', $uri->getHost());
        }
        return $request;
    }

    public function getServerParams(): array
    {
        return $this->server->all();
    }

    public function getCookieParams(): array
    {
        return $this->cookies->all();
    }

    public function withCookieParams(array $cookies): ServerRequestInterface
    {
        $request = clone $this;
        $request->cookies->replace($cookies);
        return $request;
    }

    public function getQueryParams(): array
    {
        return $this->query->all();
    }

    public function withQueryParams(array $query): ServerRequestInterface
    {
        $request = clone $this;
        $request->query->replace($query);
        return $request;
    }

    public function getUploadedFiles(): array
    {
        return $this->files->all();
    }

    public function withUploadedFiles(array $uploadedFiles): ServerRequestInterface
    {
        $request = clone $this;
        $request->files->replace($uploadedFiles);
        return $request;
    }

    public function getParsedBody()
    {
        return $this->request->all();
    }

    public function withParsedBody($data): ServerRequestInterface
    {
        $request = clone $this;
        $request->request->replace($data);
        return $request;
    }

    public function getAttributes(): array
    {
        return $this->attributes->all();
    }

    public function getAttribute(string $name, $default = null)
    {
        return $this->attributes->get($name, $default);
    }

    public function withAttribute(string $name, $value): ServerRequestInterface
    {
        $request = clone $this;
        $request->attributes->set($name, $value);
        return $request;
    }

    public function withoutAttribute(string $name): ServerRequestInterface
    {
        $request = clone $this;
        $request->attributes->remove($name);
        return $request;
    }
}
