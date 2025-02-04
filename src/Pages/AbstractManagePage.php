<?php

namespace Phntm\Lib\Pages;

use Phntm\Lib\Infra\Routing\Router;
use Phntm\Lib\Db\Db;
use Phntm\Lib\Http\Redirect;
use Phntm\Lib\Model;
use Psr\Http\Message\StreamInterface;
use Symfony\Component\HttpFoundation\Request;


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

    public function dispatch(Request $request): StreamInterface
    {
        $this->getEntityInstance();
        return parent::dispatch($request);
    }

    protected function getEntityInstance(): Model|null
    {
        $db = Db::getConnection();
        $qb = $db->createQueryBuilder();
        $qb->select('*')
            ->from($this->entityClass::getTableName());

        $result = $db
            ->executeQuery($qb->getSQL(), $qb->getParameters())
            ->fetchAssociative();

        $this->entityClass::where($this->entityResolutionIdentifier, $result[$this->entityResolutionIdentifier]);

        $entity = new $this->entityClass;

        $entity->load($result);

        dd($result, $entity);

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
        if (end($pathParts) === 'Manage') {
            array_pop($pathParts);
        }
        // remove last part of the path
        // add manage to the start of the path
        array_unshift($pathParts, 'manage');
        $route['path'] = '/' . implode('/', $pathParts);
        $route['priority'] = Router::calcRoutePriority($route['path']);
        return $route;
    }
}
