<?php

namespace Phntm\Lib\Pages;

use Doctrine\DBAL\Connection;
use Phntm\Lib\Infra\Routing\Router;
use Phntm\Lib\Model;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\ServerRequestInterface as PsrRequest;


abstract class AbstractManagePage extends RichPage implements Manageable
{
    /**
     * Attribute on the model to be used for resolution
     */
    protected string $entityResolutionIdentifier = 'id';

    protected string $entityClass;

    protected ?Model $entity = null;

    protected string|false|null $render_view = ROOT . PHNTM . 'views/manage-form.twig';

    public function preRender(): void
    {
        parent::preRender();

        if (!$this->entity) {
            $this->entity = new $this->entityClass;
        }

        $entityName = (new \ReflectionClass($this->entityClass))->getShortName();
        $this->renderWith([
            'entity' => $entityName,
            'form' => $this->formSchema(),
        ]);
    }

    abstract protected function resolveEntityIdentifier(): null|int|array;
    abstract protected function handleEntityNotFound(): void;

    public function dispatch(PsrRequest $request): StreamInterface
    {
        $this->entity = $this->getEntityInstance();
        return parent::dispatch($request);
    }

    protected function getEntityInstance(): Model|null
    {
        $identifier = $this->resolveEntityIdentifier();
        $key = array_key_first($identifier);

        /** @var Connection $db */
        $db = $this->getContainer()->get(Connection::class);
        $qb = $db->createQueryBuilder();

        $qb->select('*')
            ->from($this->entityClass::getTableName())
            ->where($key . ' = :identifier')
            ->setMaxResults(1)
            ->setParameter('identifier', $identifier[$key])
        ;

        $result = $db
            ->executeQuery($qb->getSQL(), $qb->getParameters())
            ->fetchAssociative()
        ;

        if (!$result) {
            $this->handleEntityNotFound();
            return $this->entity;
        }

        $entity = $this->entityClass::where($this->entityResolutionIdentifier, $result[$this->entityResolutionIdentifier]);

        return $entity ? $entity : null;
    }

    public function formSchema(): array
    {
        $form = [];
        $reflection = new \ReflectionClass($this->entity);
        $properties = $reflection->getProperties();
        $attributes = $this->entity->getAttributes();

        $attributes = array_filter($attributes, function ($attr) {
            return !$attr->isHidden();
        });

        foreach ($attributes as $attr) {
            $form[$attr->getColumnName()] = $attr->getFormAttributes();
        }

        return $form;
    }

    protected static function resolveBaseRoute(): array
    {
        $route = parent::resolveBaseRoute();
        $pathParts = explode('/', ltrim($route['path'], '/'));
        if (end($pathParts) === 'manage') {
            array_pop($pathParts);
        }
        // add manage to the start of the path
        array_unshift($pathParts, 'manage');
        $route['path'] = '/' . implode('/', $pathParts);
        $route['priority'] = Router::calcRoutePriority($route['path']);
        return $route;
    }
}
