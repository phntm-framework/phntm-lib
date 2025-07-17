<?php

namespace Phntm\Lib\Model;

use League\Container\ContainerAwareInterface;
use League\Container\ContainerAwareTrait;
use Phntm\Lib\Model;
use Psr\Http\Message\ServerRequestInterface;

class Factory implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    protected string $entityClass;

    public function __construct(
        protected ServerRequestInterface $request,
    ) {}

    public function forModel(Model|string $modelClass): static
    {
        $instance = $this->getContainer()->get(static::class);
        $instance->setModelClass($modelClass);

        return $instance;
    }

    protected function setModelClass(string $modelClass): void
    {
        $this->entityClass = $modelClass;
    }

    public function fromPostKey(string $key): Model
    {
        $postBody = $this->request->getParsedBody();

        $cols = $postBody[$key] ?? [];

        $model = new $this->entityClass();
        foreach ($cols as $col => $value) {
            $model->{$col} = $value;
        }

        return $model;
    }
}
